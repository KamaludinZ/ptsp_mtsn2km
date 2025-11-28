<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'file_path',
        'file_name',
        'file_type',
        'uploaded_by'
    ];

    // Relationship with ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relationship with user who uploaded
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}