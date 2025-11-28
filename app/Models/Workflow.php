<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workflow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_id',
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationship with service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Relationship with workflow steps
    public function steps()
    {
        return $this->hasMany(WorkflowStep::class);
    }

    // Relationship with ticket workflows
    public function ticketWorkflows()
    {
        return $this->hasMany(TicketWorkflow::class);
    }
}