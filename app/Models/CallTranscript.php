<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallTranscript extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_id',
        'transcript_text',
        'dialogue_json',
    ];

    protected $casts = [
        'dialogue_json' => 'array',
    ];

    public function call()
    {
        return $this->belongsTo(Call::class);
    }
}
