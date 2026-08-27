<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIAgent extends Model
{
    use HasFactory;

    protected $table = 'ai_agents';

    protected $fillable = [
        'name',
        'voice',
        'language',
        'business_name',
        'greeting',
        'personality',
        'system_instructions',
        'allowed_services',
        'faq_knowledge',
        'appointment_rules',
        'escalation_rules',
        'call_ending_message',
        'is_active',
    ];

    protected $casts = [
        'allowed_services' => 'array',
        'faq_knowledge' => 'array',
        'appointment_rules' => 'array',
        'escalation_rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function knowledgeItems()
    {
        return $this->hasMany(AIAgentKnowledge::class, 'ai_agent_id');
    }

    public function faqs()
    {
        return $this->hasMany(AIAgentFaq::class, 'ai_agent_id');
    }

    public function calls()
    {
        return $this->hasMany(Call::class, 'ai_agent_id');
    }
}
