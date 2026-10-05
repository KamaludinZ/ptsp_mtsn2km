<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        'App\Models\Ticket' => 'App\Policies\TicketPolicy',
        'App\Models\Complaint' => 'App\Policies\ComplaintPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Pengaturan Aplikasi (screen and /api/pengaturan): active administrators only.
        // Roles live on the "web" guard; API requests come in on "sanctum".
        \Illuminate\Support\Facades\Gate::define('kelola-pengaturan', fn (\App\Models\User $user) => $user->is_active !== false && $user->hasRole('admin', 'web'));
    }
}