<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Services\NotificationDispatcher;
use App\Services\TicketService;
use Illuminate\Database\Seeder;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Contoh permohonan (demo data, never seeded in production): requests in
 * every stage of Kelola Permohonan, created through TicketService so the
 * service history, status history, dispositions and incoming categories
 * are consistent. Spread over the last weeks so Laporan Bulanan has data.
 */
class TicketSeeder extends Seeder
{
    private TicketService $tickets;

    private Carbon $today;

    public function run(): void
    {
        $this->tickets = app(TicketService::class);
        $clock = Carbon::getTestNow(); // restored at the end (tests may have travelled in time)
        $muted = NotificationDispatcher::$muted;
        NotificationDispatcher::$muted = true; // demo applicants must not receive e-mail/WhatsApp

        try {
            $this->seedScenarios($clock);
        } finally {
            Carbon::setTestNow($clock);
            NotificationDispatcher::$muted = $muted;
        }
    }

    private function seedScenarios(?CarbonInterface $clock): void
    {
        $this->today = Carbon::instance($clock ?? Carbon::now())->startOfDay();

        $officer = $this->user('staff1@mtsn2malang.sch.id');
        $officer2 = $this->user('staff2@mtsn2malang.sch.id');
        $counter = $this->user('loket1@mtsn2malang.sch.id');
        $headmaster = $this->user('kepsek@mtsn2malang.sch.id');
        if (! $officer || ! $counter || ! $headmaster) {
            return; // UserSeeder has not run
        }
        $officer2 ??= $officer;

        $applicants = User::whereIn('email', [
            'ahmad.rizki.parent@gmail.com', 'siti.nurhaliza.parent@gmail.com', 'fadli.alumni@gmail.com',
            'dewi.permata.alumni@gmail.com', 'budi.santoso@email.com', 'siti.aminah@email.com',
            'admin@sdnmerdeka.sch.id', 'kerjasama@um.ac.id', 'ahmad.rizki@student.mtsn2malang.sch.id',
        ])->get()->values();
        if ($applicants->isEmpty()) {
            return;
        }
        $applicant = fn (int $i) => $applicants[$i % $applicants->count()];

        // [service, applicant, mode, days ago, request text, steps]
        $scenarios = [
            ['Legalisir Ijazah dan Transkrip Nilai', 0, 'online', 0, 'Legalisir ijazah 3 lembar untuk pendaftaran SMA.', []],
            ['Surat Keterangan Siswa Aktif', 8, 'offline', 0, 'Surat aktif untuk pengajuan KIP.', []],
            ['Surat Rekomendasi Beasiswa', 1, 'online', 2, 'Rekomendasi beasiswa prestasi kabupaten.', ['verify']],
            ['Izin Kegiatan atau Penelitian', 7, 'online', 4, 'Izin penelitian skripsi tentang literasi siswa.', ['verify', 'dispose:Untuk dikoordinasikan:waka_kurikulum,tata_usaha']],
            ['Surat Permohonan Kerjasama', 6, 'online', 6, 'Kerja sama program literasi dengan SD Merdeka.', ['verify', 'dispose:Mohon saran/pertimbangan:waka_humas']],
            ['Permohonan Data Statistik', 2, 'online', 8, 'Data jumlah siswa per kelas tahun ajaran ini.', ['verify', 'dispose:Untuk diketahui:tata_usaha', 'process']],
            ['Surat Keterangan Berkelakuan Baik', 3, 'offline', 9, 'Surat berkelakuan baik untuk melamar kerja.', ['verify', 'dispose:Untuk diproses:tata_usaha', 'assign', 'note:Pemohon diminta membawa pas foto 3x4.']],
            ['Pelayanan Perpustakaan', 8, 'online', 12, 'Surat bebas pustaka untuk kelulusan.', ['assign', 'complete']],
            ['Informasi Akademik Anak', 1, 'online', 20, 'Rekap nilai semester ganjil.', ['assign', 'overdue']],
            ['Legalisir Ijazah dan Transkrip Nilai', 4, 'offline', 15, 'Legalisir transkrip untuk pendaftaran kuliah.', ['verify', 'dispose:Untuk diproses:tata_usaha', 'assign', 'complete', 'pickup']],
            ['Surat Keterangan Siswa Aktif', 0, 'online', 25, 'Surat aktif untuk BPJS.', ['verify', 'dispose:Untuk diproses:tata_usaha', 'assign', 'complete', 'handover']],
            ['Surat Rekomendasi Beasiswa', 5, 'online', 30, 'Rekomendasi beasiswa yayasan.', ['verify', 'reject:Nilai rata-rata belum memenuhi syarat beasiswa.']],
            ['Izin Kegiatan atau Penelitian', 7, 'online', 35, 'Izin observasi kelas.', ['cancel:Pemohon membatalkan karena jadwal berubah.']],
            ['Surat Keterangan Berkelakuan Baik', 2, 'offline', 40, 'Surat berkelakuan baik untuk beasiswa.', ['verify', 'dispose:Untuk diproses:tata_usaha', 'assign', 'complete']],
            ['Permohonan Data Statistik', 6, 'online', 45, 'Data kelulusan lima tahun terakhir.', ['verify', 'dispose:Untuk diarsipkan:tata_usaha', 'assign', 'complete']],
        ];

        foreach ($scenarios as [$serviceName, $who, $mode, $daysAgo, $text, $steps]) {
            $service = Service::where('name', $serviceName)->first();
            if (! $service) {
                continue;
            }

            // Online only where the service is served online and open to the applicant's type.
            $person = $applicant($who);
            if ($mode === 'online' && ! $service->acceptsRequests($person, 'online')) {
                $person = $applicants->first(fn (User $candidate) => $service->acceptsRequests($candidate, 'online')) ?? $person;
                $mode = $service->acceptsRequests($person, 'online') ? 'online' : 'offline';
            }

            $this->at($daysAgo, 8);
            $ticket = $this->tickets->open($service, $person, $mode === 'offline' ? $counter : $person, $mode, $text);

            $hour = 9;
            foreach ($steps as $step) {
                $this->at($daysAgo, $hour++);
                $this->step($ticket->fresh(), $step, $officer, $headmaster, $daysAgo % 2 ? $officer2 : $officer, $counter);
            }
        }
    }

    private function step(Ticket $ticket, string $step, User $officer, User $headmaster, User $assignee, User $counter): void
    {
        [$action, $detail, $units] = array_pad(explode(':', $step, 3), 3, null);

        match ($action) {
            'verify' => $this->tickets->changeStatus($ticket, 'verified', 'Berkas persyaratan lengkap.', $officer),
            // The leader this service's disposition rule allows (Kepala Madrasah and/or Kepala TU).
            'dispose' => $this->tickets->dispose($ticket, app(\App\Services\DispositionAuthority::class)->disposers($ticket)->first() ?? $headmaster, 'acknowledged_by', explode(',', (string) $units), $detail),
            'assign' => $this->tickets->assign($ticket, $assignee, $officer),
            'process' => $this->tickets->changeStatus($ticket, 'in_process', 'Mulai diproses unit.', $officer),
            'note' => $this->tickets->addNote($ticket, $detail, $officer, 'contact_applicant', true),
            'complete' => $this->tickets->changeStatus($ticket, 'completed', 'Layanan selesai.', $assignee),
            'pickup' => $ticket->update(['ready_for_pickup' => true]),
            'handover' => $this->tickets->handOver($ticket->fresh(), $counter),
            'reject' => $this->tickets->changeStatus($ticket, 'rejected', $detail, $officer),
            'cancel' => $this->tickets->changeStatus($ticket, 'cancelled', $detail, $officer),
            // Target date already passed while still in process.
            'overdue' => $ticket->update(['estimated_completion_date' => now()->subDays(3)->toDateString()]),
        };
    }

    /** Pretend the next action happens $daysAgo days ago at $hour o'clock. */
    private function at(int $daysAgo, int $hour): void
    {
        Carbon::setTestNow($this->today->copy()->subDays($daysAgo)->setTime(min($hour, 16), 0));
    }

    private function user(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
