<?php

namespace App\Http\Controllers;

use App\Models\TicketFile;
use App\Models\TicketOutput;
use App\Support\TicketDocuments;

/**
 * Authorized downloads of ticket documents (requirement files and service
 * outputs), which are no longer reachable through public /storage URLs.
 */
class TicketDocumentController extends Controller
{
    public function file(TicketFile $file)
    {
        $this->authorize('view', $file->ticket);

        return TicketDocuments::download($file->file_path, $file->file_name);
    }

    public function output(TicketOutput $output)
    {
        $this->authorize('view', $output->ticket);

        return TicketDocuments::download($output->file_path, $output->file_name ?? null);
    }
}
