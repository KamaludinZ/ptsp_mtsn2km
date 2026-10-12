<?php

namespace App\Services;

use App\Filament\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Support\Collection;

/**
 * Notifikasi pengaduan, saran & WBS (Modul 10): the reporter hears that the
 * report arrived and how it is followed up; the complaint handlers (admin,
 * kepala madrasah, kepala TU) hear of every new report. Messages to staff
 * never carry the reporter's identity, and a secret report's subject is
 * withheld from them too: WhatsApp and e-mail leave the application.
 */
class ComplaintNotifier
{
    public function __construct(private NotificationDispatcher $dispatcher)
    {
    }

    /** A report was just sent from the public pages. */
    public function received(Complaint $complaint): void
    {
        $this->toReporter('complaint_received', $complaint);

        foreach ($this->handlers() as $handler) {
            $this->dispatcher->sendTo('complaint_new', $this->values($complaint, $handler->name, [
                'tautan' => ComplaintResource::getUrl('view', ['record' => $complaint], panel: 'admin'),
            ]), $handler->email, $handler->whatsapp_number, ['complaint_id' => $complaint->id, 'user_id' => $handler->id]);
        }
    }

    /** The stage changed or the handlers replied; $reply is the new reply, if any. */
    public function updated(Complaint $complaint, ?string $reply = null): void
    {
        $this->toReporter('complaint_updated', $complaint, ['catatan' => filled($reply) ? 'Tanggapan petugas: ' . $reply : '']);
    }

    /** @return Collection<int, User> active staff holding a complaint-handler role */
    public function handlers(): Collection
    {
        return User::role(RoleAccess::COMPLAINT_HANDLERS)->where('is_active', true)->orderBy('id')->get();
    }

    private function toReporter(string $event, Complaint $complaint, array $extra = []): void
    {
        // An anonymous whistleblower left no contact; never reach out to one.
        if ($complaint->complaint_type === 'whistleblowing' && $complaint->anonymous) {
            return;
        }

        $this->dispatcher->sendTo(
            $event,
            $this->values($complaint, $complaint->reporter_name ?: 'Bapak/Ibu', ['tautan' => route('supervision.complaint.track.form')] + $extra),
            $complaint->reporter_email,
            $complaint->reporter_phone,
            ['complaint_id' => $complaint->id, 'user_id' => $complaint->user_id],
        );
    }

    private function values(Complaint $complaint, string $name, array $extra = []): array
    {
        return array_merge([
            'nama' => $name,
            'nomor_laporan' => $complaint->complaint_number,
            'jenis_laporan' => $complaint->typeLabel(),
            'judul' => $complaint->isSecret() ? '(dirahasiakan, buka aplikasi untuk membaca)' : (string) $complaint->title,
            'status' => $complaint->statusLabel(),
            'catatan' => '',
            'instansi' => app_brand_name(),
        ], $extra);
    }
}
