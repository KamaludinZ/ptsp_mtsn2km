<?php

namespace App\Livewire;

use App\Support\PasswordRotation;
use Livewire\Component;

/** Banner pengingat ganti kata sandi di atas isi halaman /cp dan /portal. */
class PasswordRotationBanner extends Component
{
    public bool $dismissed = false;

    public function dismiss(): void
    {
        PasswordRotation::dismiss(auth()->user());
        $this->dismissed = true;
    }

    public function render()
    {
        $reason = $this->dismissed ? null : PasswordRotation::reason(auth()->user());

        return view('livewire.password-rotation-banner', [
            'reason' => $reason,
            'message' => $reason ? PasswordRotation::message($reason) : null,
            'profileUrl' => $reason ? filament()->getProfileUrl() : null,
        ]);
    }
}
