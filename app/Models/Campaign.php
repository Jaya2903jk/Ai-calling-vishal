<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ai_agent_id',
        'call_purpose',
        'scheduled_start',
        'calling_hours_start',
        'calling_hours_end',
        'status',
        'total_contacts',
        'completed_calls',
        'failed_calls',
        'conversions',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
    ];

    public function aiAgent()
    {
        return $this->belongsTo(AIAgent::class, 'ai_agent_id');
    }

    public function contacts()
    {
        return $this->hasMany(CampaignContact::class);
    }
}
