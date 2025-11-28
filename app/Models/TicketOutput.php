<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketOutput extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'output_type',
        'file_path',
        'output_description',
        'is_delivered',
        'delivery_date',
        'delivery_method',
        'delivered_to'
    ];

    protected $casts = [
        'is_delivered' => 'boolean',
        'delivery_date' => 'date'
    ];

    // Relationship with ticket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relationship with user who received the output
    public function deliveredTo()
    {
        return $this->belongsTo(User::class, 'delivered_to');
    }

    // Constants for output types
    const OUTPUT_TYPE_DIGITAL = 'digital';
    const OUTPUT_TYPE_PHYSICAL = 'physical';

    // Constants for delivery methods
    const DELIVERY_EMAIL = 'email';
    const DELIVERY_WHATSAPP = 'whatsapp';
    const DELIVERY_PHYSICAL_COLLECTION = 'physical_collection';
}