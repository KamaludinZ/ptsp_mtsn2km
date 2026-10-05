<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kategori layanan masuk as data: disposition_category is set when the
 * leader disposes a request (from the instruction), incoming_category when
 * staff choose one. The effective category is
 * coalesce(incoming_category, disposition_category, 'disposisi'), indexed
 * for the Layanan Masuk tabs. Existing tickets are backfilled from the
 * instruction text kept in approval_notes ("Instruksi: … .").
 */
return new class extends Migration
{
    /** Instruction => category (as App\Support\IncomingCategory::INSTRUCTION_CATEGORIES). */
    private const INSTRUCTIONS = [
        'Untuk diketahui' => 'tembusan',
        'Untuk diarsipkan' => 'tembusan',
        'Untuk dikoordinasikan' => 'koordinasi',
        'Mohon saran/pertimbangan' => 'arahan',
    ];

    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('disposition_category', 20)->nullable();
        });

        foreach (self::INSTRUCTIONS as $instruction => $category) {
            DB::table('tickets')
                ->whereNull('disposition_category')
                ->whereRaw('lower(approval_notes) like ?', ['%' . mb_strtolower('Instruksi: ' . $instruction . '.') . '%'])
                ->update(['disposition_category' => $category]);
        }
        DB::table('tickets')->whereNull('disposition_category')->where('approval_status', 'approved')->update(['disposition_category' => 'disposisi']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("alter table tickets add constraint tickets_disposition_category_check check (disposition_category is null or disposition_category in ('disposisi','tembusan','koordinasi','arahan'))");
            DB::statement("create index tickets_effective_category_index on tickets ((coalesce(incoming_category, disposition_category, 'disposisi')))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('drop index if exists tickets_effective_category_index');
            DB::statement('alter table tickets drop constraint if exists tickets_disposition_category_check');
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('disposition_category');
        });
    }
};
