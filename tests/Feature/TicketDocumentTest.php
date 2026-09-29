<?php

namespace Tests\Feature;

use App\Filament\Portal\Pages\ApplyService;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketDocumentTest extends TestCase
{
    use RefreshDatabase;

    private function backOffice(): User
    {
        $staff = User::factory()->create(['user_type' => 'pegawai']);
        $staff->assignRole(Role::findOrCreate('back_office'));
        $staff->givePermissionTo(Permission::findOrCreate('backoffice.access'));

        return $staff;
    }

    public function test_uploaded_requirements_are_private_and_only_served_to_owner_and_staff(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $applicant = User::factory()->create(['user_type' => 'umum']);
        $service = Service::factory()->create(['user_types_allowed' => ['umum']]);

        Filament::setCurrentPanel(Filament::getPanel('portal'));
        $this->actingAs($applicant);
        Livewire::withQueryParams(['layanan' => $service->slug])
            ->test(ApplyService::class)
            ->fillForm([
                'description' => 'Legalisir ijazah.',
                'priority' => 'normal',
                'files' => [UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf')],
            ])
            ->call('submit')
            ->assertHasNoFormErrors();

        $file = Ticket::where('user_id', $applicant->id)->firstOrFail()->files()->firstOrFail();

        $this->assertSame('ktp.pdf', $file->file_name);
        Storage::disk('local')->assertExists($file->file_path);
        Storage::disk('public')->assertMissing($file->file_path);

        $this->actingAs($applicant)->get(route('documents.ticket-file', $file))->assertOk();

        $stranger = User::factory()->create(['user_type' => 'umum']);
        $this->actingAs($stranger)->get(route('documents.ticket-file', $file))->assertForbidden();

        $this->actingAs($this->backOffice())->get(route('documents.ticket-file', $file))->assertOk();
    }

    public function test_output_upload_rejects_executable_files(): void
    {
        Storage::fake('local');

        $ticket = Ticket::factory()->create(['status' => 'in_process', 'approval_required' => false]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->backOffice());
        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->callAction('uploadOutput', ['file' => UploadedFile::fake()->create('shell.php', 1, 'application/x-php')])
            ->assertHasActionErrors(['file']);

        $this->assertNull($ticket->fresh()->output);
        $this->assertSame('in_process', $ticket->fresh()->status);
    }

    public function test_output_upload_completes_the_ticket_for_the_applicant(): void
    {
        Storage::fake('local');

        $ticket = Ticket::factory()->create(['status' => 'in_process', 'mode' => 'online', 'approval_required' => false]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->backOffice());
        Livewire::test(ViewTicket::class, ['record' => $ticket->id])
            ->callAction('uploadOutput', ['file' => UploadedFile::fake()->create('surat.pdf', 50, 'application/pdf')])
            ->assertHasNoActionErrors();

        $ticket->refresh();
        $this->assertSame('completed', $ticket->status);
        $this->assertNotNull($ticket->output);
        Storage::disk('local')->assertExists($ticket->output->file_path);

        $this->actingAs($ticket->user)->get(route('documents.ticket-output', $ticket->output))->assertOk();
    }
}
