<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallOutcome extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_id',
        'outcome',
        'confidence_score',
        'notes',
        'updated_by_user_id',
    ];

    public function call()
    {
        return $this->belongsTo(Call::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
