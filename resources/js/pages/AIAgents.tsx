import React, { useState, useEffect } from 'react';
import { Bot, Plus, Sparkles, Volume2, Globe, Shield, CheckCircle2 } from 'lucide-react';
import api from '../api/client';

export const AIAgents: React.FC = () => {
    const [agents, setAgents] = useState<any[]>([]);

    useEffect(() => {
        api.get('/ai-agents').then((res) => {
            if (res.data.success) {
                setAgents(res.data.data);
            }
        }).catch(() => {});
    }, []);

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-xl font-bold text-white">AI Voice Agents Setup</h1>
                    <p className="text-xs text-slate-400">Configure multi-lingual voice personalities, greetings, system instructions, and booking rules</p>
                </div>
            </div>

            {/* Agents Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {agents.map((agent) => (
                    <div key={agent.id} className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                                <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 p-0.5 shadow-md shadow-cyan-500/20">
                                    <div className="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center">
                                        <Bot className="w-6 h-6 text-cyan-400" />
                                    </div>
                                </div>
                                <div>
                                    <h3 className="text-base font-extrabold text-white">{agent.name}</h3>
                                    <p className="text-xs text-cyan-400 font-medium">{agent.language} • Voice: {agent.voice}</p>
                                </div>
                            </div>
                            <span className="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Active & Ready
                            </span>
                        </div>

                        <div className="space-y-2 text-xs">
                            <div className="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                                <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Greeting</span>
                                <p className="text-slate-200 italic">"{agent.greeting}"</p>
                            </div>
                            <div className="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                                <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Personality</span>
                                <p className="text-slate-300">{agent.personality}</p>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
};
