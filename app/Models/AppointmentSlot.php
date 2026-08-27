<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'service_id',
        'is_booked',
        'locked_until',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'is_booked' => 'boolean',
        'locked_until' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
