<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIAgentKnowledge extends Model
{
    use HasFactory;

    protected $table = 'ai_agent_knowledge';

    protected $fillable = [
        'ai_agent_id',
        'category',
        'title',
        'content',
    ];

    public function aiAgent()
    {
        return $this->belongsTo(AIAgent::class, 'ai_agent_id');
    }
}
