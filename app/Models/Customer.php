<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'gender',
        'dob',
        'location',
        'source',
        'lead_status',
        'interested_service_id',
        'notes',
        'assigned_user_id',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'interested_service_id');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function calls()
    {
        return $this->hasMany(Call::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function notesList()
    {
        return $this->hasMany(CustomerNote::class)->latest();
    }

    public function activities()
    {
        return $this->hasMany(CustomerActivity::class)->latest();
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }
}
