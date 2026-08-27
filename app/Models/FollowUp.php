<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'scheduled_at',
        'reason',
        'status',
        'auto_ai_call',
        'retry_count',
        'max_retries',
        'call_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'auto_ai_call' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function call()
    {
        return $this->belongsTo(Call::class);
    }
}
