<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_id',
        'purpose',
        'customer_interest',
        'main_discussion',
        'questions_asked',
        'objections_raised',
        'requested_service',
        'preferred_date',
        'preferred_time',
        'appointment_status',
        'follow_up_required',
        'recommended_next_action',
    ];

    protected $casts = [
        'follow_up_required' => 'boolean',
    ];

    public function call()
    {
        return $this->belongsTo(Call::class);
    }
}
