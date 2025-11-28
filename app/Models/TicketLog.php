<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'action',
        'performed_by',
        'from_status',
        'to_status',
        'notes'
    ];

    // Relationship with ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relationship with user who performed the action
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}