<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Riwayat checkout tamu: an officer's checkout or the guest's own (insert only, see the migration). */
class VisitorCheckout extends Model
{
    public const METHODS = [
        'petugas' => 'Oleh petugas',
        'mandiri' => 'Mandiri dari buku tamu',
    ];

    public const UPDATED_AT = null;

    protected $fillable = ['visitor_id', 'method', 'checked_out_by', 'ip_address', 'checked_out_at'];

    protected $casts = ['checked_out_at' => 'datetime'];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class)->withTrashed();
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }
}
