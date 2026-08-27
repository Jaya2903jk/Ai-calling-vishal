<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallQueue extends Model
{
    use HasFactory;

    protected $table = 'call_queue';

    protected $fillable = [
        'call_id',
        'campaign_id',
        'priority',
        'status',
        'attempts',
        'available_at',
    ];

    protected $casts = [
        'available_at' => 'datetime',
    ];

    public function call()
    {
        return $this->belongsTo(Call::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
