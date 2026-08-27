import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { User, Phone, Mail, MapPin, Calendar, Clock, Bot, Sparkles, CheckCircle2, Volume2, ShieldCheck } from 'lucide-react';
import api from '../api/client';
import { AudioPlayer } from '../components/AudioPlayer';

export const CallDetail: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const [call, setCall] = useState<any>(null);

    useEffect(() => {
        api.get(`/calls/${id}`).then((res) => {
            if (res.data.success) {
                setCall(res.data.call);
            }
        }).catch(() => {});
    }, [id]);

    if (!call) {
        return <div className="p-8 text-center text-slate-400 font-mono">Loading call details...</div>;
    }

    return (
        <div className="space-y-6">
            {/* Top Waveform Audio Player Header */}
            <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-extrabold text-white flex items-center gap-2">
                            AI Call Details & Recording #{call.id}
                            <span className="px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                {call.status}
                            </span>
                        </h1>
                        <p className="text-xs text-slate-400 mt-0.5">{call.call_purpose} • Phone: {call.phone_number}</p>
                    </div>
                </div>

                <AudioPlayer src={call.recording?.recording_url} title={`Call Audio Recording (${call.duration_seconds}s)`} duration={call.duration_seconds || 39} />
            </div>

            {/* 3-Pane Professional Call Detail Layout */}
            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {/* LEFT PANE: Customer Information */}
                <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                    <h3 className="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center gap-2">
                        <User className="w-4 h-4 text-cyan-400" /> Customer Info
                    </h3>
                    <div className="space-y-3 text-xs">
                        <div>
                            <span className="text-slate-400 block">Name</span>
                            <span className="font-bold text-white text-sm">{call.customer?.name}</span>
                        </div>
                        <div>
                            <span className="text-slate-400 block">Phone</span>
                            <span className="font-semibold text-cyan-400">{call.customer?.phone}</span>
                        </div>
                        <div>
                            <span className="text-slate-400 block">Email</span>
                            <span className="text-slate-300">{call.customer?.email || 'N/A'}</span>
                        </div>
                        <div>
                            <span className="text-slate-400 block">Location</span>
                            <span className="text-slate-300">{call.customer?.location || 'India'}</span>
                        </div>
                        <div>
                            <span className="text-slate-400 block">Lead Status</span>
                            <span className="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                {call.customer?.lead_status}
                            </span>
                        </div>
                    </div>
                </div>

                {/* CENTER PANE: Conversation Transcript */}
                <div className="lg:col-span-2 p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                    <h3 className="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center justify-between">
                        <span className="flex items-center gap-2"><Sparkles className="w-4 h-4 text-cyan-400" /> Speech Transcript</span>
                        <span className="text-[10px] text-emerald-400 font-mono">Speaker Labels Active</span>
                    </h3>

                    <div className="space-y-3 max-h-[450px] overflow-y-auto pr-2">
                        {call.transcript?.dialogue_json?.map((turn: any, idx: number) => (
                            <div key={idx} className={`flex items-start gap-3 ${turn.speaker === 'AI' ? 'pl-2' : 'pr-2'}`}>
                                <div className={`w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 ${
                                    turn.speaker === 'AI'
                                        ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30'
                                        : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                                }`}>
                                    {turn.speaker === 'AI' ? 'AI' : 'C'}
                                </div>
                                <div className="flex-1 bg-slate-950/80 p-3 rounded-2xl border border-slate-800/80">
                                    <div className="flex items-center justify-between mb-1">
                                        <span className="text-xs font-bold text-white">{turn.speaker === 'AI' ? call.ai_agent?.name || 'AI Assistant' : call.customer?.name}</span>
                                        <span className="text-[10px] font-mono text-slate-500">{turn.timestamp}</span>
                                    </div>
                                    <p className="text-xs text-slate-300 leading-relaxed">{turn.text}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* RIGHT PANE: Call Information & AI Summary */}
                <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                    <h3 className="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800 pb-3">
                        Call Summary & Outcome
                    </h3>

                    <div className="space-y-3 text-xs">
                        <div className="p-3 rounded-xl bg-slate-950 border border-slate-800">
                            <span className="text-slate-400 block text-[10px] uppercase font-bold">Outcome</span>
                            <span className="font-extrabold text-cyan-400 text-sm">{call.outcome?.outcome || 'Appointment Booked'}</span>
                        </div>

                        {call.summary && (
                            <div className="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1.5">
                                <span className="text-slate-400 block text-[10px] uppercase font-bold">Main Discussion</span>
                                <p className="text-slate-300">{call.summary.main_discussion}</p>
                            </div>
                        )}

                        {call.appointment && (
                            <div className="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 space-y-1">
                                <span className="text-emerald-400 block text-[10px] uppercase font-bold flex items-center gap-1">
                                    <CheckCircle2 className="w-3.5 h-3.5" /> Appointment Confirmed
                                </span>
                                <p className="text-white font-bold">{call.appointment.date} @ {call.appointment.start_time}</p>
                                <p className="text-[11px] text-slate-300">{call.appointment.service?.name}</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};
