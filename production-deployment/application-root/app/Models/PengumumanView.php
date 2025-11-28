<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengumumanView extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengumuman_id',
        'ip_address',
    ];


    public function pengumuman()
    {
        return $this->belongsTo(Pengumuman::class);
    }
}
