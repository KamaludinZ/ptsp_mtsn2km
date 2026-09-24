<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TicketDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_requirements_are_private_and_only_served_to_owner_and_staff(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $applicant = User::factory()->create(['user_type' => 'umum']);
        $service = Service::factory()->create(['user_types_allowed' => ['umum']]);

        $this->actingAs($applicant)->post("/services/{$service->slug}/apply", [
            'description' => 'Legalisir ijazah.',
            'files' => [UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf')],
        ])->assertRedirect();

        $file = Ticket::where('user_id', $applicant->id)->firstOrFail()->files()->firstOrFail();

        Storage::disk('local')->assertExists($file->file_path);
        Storage::disk('public')->assertMissing($file->file_path);

        $this->actingAs($applicant)->get(route('documents.ticket-file', $file))->assertOk();

        $stranger = User::factory()->create(['user_type' => 'umum']);
        $this->actingAs($stranger)->get(route('documents.ticket-file', $file))->assertForbidden();

        $staff = User::factory()->create(['user_type' => 'pegawai']);
        $staff->givePermissionTo(Permission::findOrCreate('backoffice.access'));
        $this->actingAs($staff)->get("/backoffice/tickets/{$file->ticket_id}/download-file/{$file->id}")->assertOk();
    }

    public function test_output_upload_rejects_executable_files(): void
    {
        Storage::fake('local');

        $staff = User::factory()->create(['user_type' => 'pegawai']);
        $staff->givePermissionTo(Permission::findOrCreate('backoffice.access'));
        $ticket = Ticket::factory()->create();

        $this->actingAs($staff)
            ->post("/backoffice/tickets/{$ticket->id}/upload-output", [
                'output_file' => UploadedFile::fake()->create('shell.php', 1, 'application/x-php'),
            ])
            ->assertSessionHasErrors('output_file');
    }
}
