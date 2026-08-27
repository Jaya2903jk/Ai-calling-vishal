<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallRecording extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_id',
        'customer_id',
        'recording_url',
        'duration',
        'file_size',
        'consent_given',
        'retention_days',
    ];

    protected $casts = [
        'consent_given' => 'boolean',
    ];

    public function call()
    {
        return $this->belongsTo(Call::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
