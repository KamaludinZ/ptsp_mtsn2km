<?php

namespace App\Http\Controllers;

use App\Models\TicketFile;
use App\Models\TicketOutput;
use App\Support\TicketDocuments;
use Illuminate\Http\Request;

/**
 * Authorized downloads of ticket documents (requirement files and service
 * outputs), which are no longer reachable through public /storage URLs.
 * ?pratinjau=1 shows a PDF in the page for the pratinjau modal.
 */
class TicketDocumentController extends Controller
{
    public function file(Request $request, TicketFile $file)
    {
        $this->authorize('view', $file->ticket);

        return $request->boolean('pratinjau')
            ? TicketDocuments::preview($file->file_path, $file->file_name)
            : TicketDocuments::download($file->file_path, $file->file_name);
    }

    public function output(Request $request, TicketOutput $output)
    {
        $this->authorize('view', $output->ticket);

        return $request->boolean('pratinjau')
            ? TicketDocuments::preview($output->file_path, $output->file_name ?? null)
            : TicketDocuments::download($output->file_path, $output->file_name ?? null);
    }
}
