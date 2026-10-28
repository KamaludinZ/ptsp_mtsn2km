<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Buku tamu terpadu: the counter and the public visitor book register two
 * kinds of guest, a visiting guest (kunjungan) and an applicant come for a
 * PTSP service (pemohon). So far an applicant was told apart only by the
 * "Pemohon Layanan: …" text of the purpose, and the applicant's status was
 * asked but never kept. The kind, the status and the service are columns
 * now; existing applicants are recognised by that text, and their service
 * by its exact name. The purpose stays as it was shown.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->string('guest_type', 20)->default('kunjungan')->after('name');
            $table->string('applicant_type', 30)->nullable()->after('guest_type');
            $table->foreignId('service_id')->nullable()->after('applicant_type')->constrained('services')->nullOnDelete();
            $table->index('check_in_time', 'visitors_check_in_time_idx');
        });

        DB::statement("ALTER TABLE visitors ADD CONSTRAINT visitors_guest_type_check CHECK (guest_type IN ('kunjungan', 'pemohon'))");
        DB::statement("ALTER TABLE visitors ADD CONSTRAINT visitors_applicant_type_check CHECK (applicant_type IS NULL OR applicant_type IN ('siswa', 'wali_murid', 'alumni', 'pegawai', 'umum', 'instansi_perusahaan'))");

        DB::statement("UPDATE visitors SET guest_type = 'pemohon' WHERE purpose LIKE 'Pemohon Layanan: %'");
        DB::statement("UPDATE visitors SET service_id = services.id FROM services
            WHERE visitors.guest_type = 'pemohon' AND services.deleted_at IS NULL AND services.name = substring(visitors.purpose FROM 18)");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE visitors DROP CONSTRAINT IF EXISTS visitors_applicant_type_check');
        DB::statement('ALTER TABLE visitors DROP CONSTRAINT IF EXISTS visitors_guest_type_check');
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropIndex('visitors_check_in_time_idx');
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn(['guest_type', 'applicant_type']);
        });
    }
};
