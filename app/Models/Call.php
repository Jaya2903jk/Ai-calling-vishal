<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'ai_agent_id',
        'phone_number',
        'call_purpose',
        'status',
        'start_time',
        'end_time',
        'duration_seconds',
        'external_call_id',
        'initiated_by_campaign_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiAgent()
    {
        return $this->belongsTo(AIAgent::class, 'ai_agent_id');
    }

    public function transcript()
    {
        return $this->hasOne(CallTranscript::class);
    }

    public function recording()
    {
        return $this->hasOne(CallRecording::class);
    }

    public function summary()
    {
        return $this->hasOne(CallSummary::class);
    }

    public function outcome()
    {
        return $this->hasOne(CallOutcome::class);
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }
}
