<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Ticket requirement files and service outputs used to be stored on the
     * public disk, i.e. downloadable from /storage/... without logging in.
     * Move them to the private disk; downloads go through authorized routes.
     */
    public function up(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        foreach (['ticket_files', 'ticket_outputs'] as $table) {
            DB::table($table)->whereNotNull('file_path')->orderBy('id')->each(function ($row) use ($public, $private) {
                $path = $row->file_path;

                if ($public->exists($path) && ! $private->exists($path)) {
                    $private->put($path, $public->get($path));
                    $public->delete($path);
                }
            });
        }
    }

    public function down(): void
    {
        // Files stay private; TicketDocuments still reads legacy public paths.
    }
};
