<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIAgentFaq extends Model
{
    use HasFactory;

    protected $table = 'ai_agent_faqs';

    protected $fillable = [
        'ai_agent_id',
        'question',
        'answer',
        'keywords',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function aiAgent()
    {
        return $this->belongsTo(AIAgent::class, 'ai_agent_id');
    }
}
