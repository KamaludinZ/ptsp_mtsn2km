{{--
    Lonceng notifikasi with the unread badge, for the public site's navbar.
    Links to the user's Notifikasi page (staff panel or applicant portal).
--}}
@props(['user'])
@php
    $unread = $user->unreadNotifications()->count();
    $url = $user->isStaff()
        ? \App\Filament\Pages\Notifications::getUrl(panel: 'admin')
        : \App\Filament\Portal\Pages\Notifications::getUrl(panel: 'portal');
    $label = $unread ? "Notifikasi, {$unread} belum dibaca" : 'Notifikasi, tidak ada yang belum dibaca';
@endphp
<a href="{{ $url }}" {{ $attributes->merge(['class' => 'site-icon-btn notification-bell']) }} aria-label="{{ $label }}" title="{{ $label }}">
    <i class="fas fa-bell" aria-hidden="true"></i>
    @if ($unread)
        <span class="notification-bell__badge" data-unread="{{ $unread }}" aria-hidden="true">{{ $unread > 99 ? '99+' : $unread }}</span>
    @endif
</a>
@once
    <style>
        .notification-bell { position: relative; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
        .notification-bell__badge {
            position: absolute; top: -4px; right: -6px; min-width: 1.15rem; height: 1.15rem; padding: 0 .3rem;
            border-radius: 9999px; background: #dc2626; color: #fff; font-size: .65rem; font-weight: 700; line-height: 1.15rem;
            text-align: center; box-shadow: 0 0 0 2px var(--bs-body-bg, #fff);
        }
    </style>
@endonce
