<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Ticket;
use App\Models\Complaint;
use App\Models\User;
use Carbon\Carbon;

class RecentActivityWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-activity-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function getRecentActivities(): array
    {
        $ticketActivities = Ticket::with(['user', 'service'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'type' => 'ticket',
                    'title' => 'Tiket #' . $ticket->ticket_number,
                    'description' => 'Status: ' . $this->getStatusText($ticket->status),
                    'user' => $ticket->user?->name ?? 'N/A',
                    'service' => $ticket->service?->name ?? 'N/A',
                    'date' => $ticket->updated_at->diffForHumans(),
                    'timestamp' => $ticket->updated_at->getTimestamp(),
                    'color' => $this->getStatusColor($ticket->status),
                    'url' => route('filament.admin.resources.tickets.edit', ['record' => $ticket]),
                ];
            });

        $complaintActivities = Complaint::latest('updated_at')
            ->limit(3)
            ->get()
            ->map(function ($complaint) {
                return [
                    'type' => 'complaint',
                    'title' => 'Pengaduan: ' . ($complaint->subject ?? $complaint->title),
                    'description' => 'Status: ' . $this->getComplaintStatusText($complaint->status),
                    'user' => $complaint->reporter_name ?? $complaint->complainant_name ?? 'Anonim',
                    'service' => 'Pengaduan',
                    'date' => $complaint->updated_at->diffForHumans(),
                    'timestamp' => $complaint->updated_at->getTimestamp(),
                    'color' => $this->getComplaintStatusColor($complaint->status),
                    'url' => route('filament.admin.resources.complaints.edit', ['record' => $complaint]),
                ];
            });

        $userActivities = User::latest('updated_at')
            ->limit(2)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'user',
                    'title' => 'Pengguna Baru: ' . $user->name,
                    'description' => 'Tipe: ' . $this->getUserTypeText($user->user_type),
                    'user' => $user->name,
                    'service' => 'Akun Pengguna',
                    'date' => $user->updated_at->diffForHumans(),
                    'timestamp' => $user->updated_at->getTimestamp(),
                    'color' => 'success',
                    'url' => route('filament.admin.resources.users.edit', ['record' => $user]),
                ];
            });

        $allActivities = $ticketActivities->concat($complaintActivities)->concat($userActivities);

        // Sort by date and return the most recent 8
        return $allActivities
            ->sortByDesc('timestamp')
            ->take(8)
            ->toArray();
    }

    private function getStatusText(string $status): string
    {
        $statusMap = [
            'submitted' => 'Diajukan',
            'verified' => 'Diverifikasi',
            'in_process' => 'Diproses',
            'pending_approval' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    private function getStatusColor(string $status): string
    {
        $colorMap = [
            'submitted' => 'gray',
            'verified' => 'info',
            'in_process' => 'warning',
            'pending_approval' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        return $colorMap[$status] ?? 'gray';
    }

    private function getComplaintStatusText(string $status): string
    {
        $statusMap = [
            'pending' => 'Menunggu',
            'investigating' => 'Ditindaklanjuti',
            'resolved' => 'Selesai',
            'rejected' => 'Ditolak',
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    private function getComplaintStatusColor(string $status): string
    {
        $colorMap = [
            'pending' => 'warning',
            'investigating' => 'info',
            'resolved' => 'success',
            'rejected' => 'danger',
        ];

        return $colorMap[$status] ?? 'gray';
    }

    private function getUserTypeText(string $userType): string
    {
        $typeMap = [
            'guru' => 'Guru',
            'pegawai' => 'Pegawai',
            'siswa' => 'Siswa',
            'walimurid' => 'Wali Murid',
            'alumni' => 'Alumni',
            'instansi' => 'Instansi',
            'umum' => 'Umum',
        ];

        return $typeMap[$userType] ?? ucfirst($userType);
    }
}