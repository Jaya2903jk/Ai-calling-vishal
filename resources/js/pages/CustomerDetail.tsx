import React, { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { User, Phone, Mail, MapPin, Calendar, Clock, Sparkles, PhoneCall, CheckCircle2, MessageSquare, Play, ShieldAlert } from 'lucide-react';
import api from '../api/client';
import { AudioPlayer } from '../components/AudioPlayer';

export const CustomerDetail: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const [customer, setCustomer] = useState<any>(null);
    const [noteText, setNoteText] = useState('');

    const fetchCustomer = () => {
        api.get(`/customers/${id}`).then((res) => {
            if (res.data.success) {
                setCustomer(res.data.customer);
            }
        }).catch(() => {});
    };

    useEffect(() => {
        fetchCustomer();
    }, [id]);

    if (!customer) {
        return <div className="p-8 text-center text-slate-400 font-mono">Loading customer profile...</div>;
    }

    const handleAddNote = async () => {
        if (!noteText.trim()) return;
        await api.post(`/customers/${id}/notes`, { note: noteText });
        setNoteText('');
        fetchCustomer();
    };

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 p-0.5 flex items-center justify-center">
                        <div className="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center font-bold text-lg text-cyan-400">
                            {customer.name.charAt(0)}
                        </div>
                    </div>
                    <div>
                        <div className="flex items-center gap-3">
                            <h1 className="text-xl font-extrabold text-white">{customer.name}</h1>
                            <span className="px-2.5 py-0.5 rounded-md text-xs font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                {customer.lead_status}
                            </span>
                        </div>
                        <div className="flex items-center gap-4 text-xs text-slate-400 mt-1">
                            <span className="flex items-center gap-1"><Phone className="w-3.5 h-3.5 text-cyan-400" /> {customer.phone}</span>
                            {customer.email && <span className="flex items-center gap-1"><Mail className="w-3.5 h-3.5 text-cyan-400" /> {customer.email}</span>}
                            {customer.location && <span className="flex items-center gap-1"><MapPin className="w-3.5 h-3.5 text-cyan-400" /> {customer.location}</span>}
                        </div>
                    </div>
                </div>

                <Link
                    to="/calls"
                    className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-semibold text-xs shadow-lg shadow-cyan-500/25 flex items-center gap-2"
                >
                    <PhoneCall className="w-4 h-4" />
                    <span>View AI Calls</span>
                </Link>
            </div>

            {/* Grid Layout */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Left: Call History & Audio Recordings */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Call Recordings & AI Summaries */}
                    <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                        <h3 className="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                            <PhoneCall className="w-4 h-4 text-cyan-400" />
                            AI Call Recordings & Summaries
                        </h3>

                        {customer.calls?.length === 0 ? (
                            <p className="text-xs text-slate-500 text-center py-6">No calls executed yet.</p>
                        ) : (
                            customer.calls?.map((call: any) => (
                                <div key={call.id} className="p-5 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h4 className="text-xs font-bold text-white">{call.call_purpose}</h4>
                                            <p className="text-[11px] text-slate-400">{new Date(call.created_at).toLocaleString()} • Duration: {call.duration_seconds}s</p>
                                        </div>
                                        <span className="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            {call.outcome?.outcome || 'Appointment Booked'}
                                        </span>
                                    </div>

                                    {/* Audio Player */}
                                    <AudioPlayer src={call.recording?.recording_url} title={`Call Record #${call.id}`} duration={call.duration_seconds} />

                                    {/* Call Summary */}
                                    {call.summary && (
                                        <div className="p-3.5 rounded-xl bg-slate-900 border border-slate-800/80 space-y-2">
                                            <h5 className="text-[11px] font-bold text-cyan-400 uppercase tracking-wider">AI Summary</h5>
                                            <p className="text-xs text-slate-300">{call.summary.main_discussion}</p>
                                            <div className="text-[11px] text-emerald-400 font-semibold pt-1 border-t border-slate-800">
                                                Next Action: {call.summary.recommended_next_action}
                                            </div>
                                        </div>
                                    )}

                                    {/* Transcript Preview */}
                                    {call.transcript && (
                                        <div className="p-3.5 rounded-xl bg-slate-900 border border-slate-800/80 space-y-2">
                                            <h5 className="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Transcript Highlights</h5>
                                            <div className="space-y-1.5 max-h-36 overflow-y-auto pr-1 text-xs">
                                                {call.transcript.dialogue_json?.map((turn: any, idx: number) => (
                                                    <div key={idx} className="flex items-start gap-2">
                                                        <span className="font-bold text-cyan-400 min-w-8">{turn.speaker}:</span>
                                                        <span className="text-slate-300">{turn.text}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            ))
                        )}
                    </div>
                </div>

                {/* Right: Notes & Activity Timeline */}
                <div className="space-y-6">
                    {/* Add Note */}
                    <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-3">
                        <h3 className="text-sm font-bold text-white flex items-center gap-2">
                            <MessageSquare className="w-4 h-4 text-cyan-400" />
                            Employee Notes
                        </h3>
                        <textarea
                            rows={3}
                            value={noteText}
                            onChange={(e) => setNoteText(e.target.value)}
                            placeholder="Add employee follow-up notes..."
                            className="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-slate-200 focus:outline-none focus:border-cyan-500/50"
                        />
                        <button
                            onClick={handleAddNote}
                            className="w-full py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-md shadow-cyan-500/20 cursor-pointer"
                        >
                            Save Note
                        </button>
                    </div>

                    {/* Customer Activity Timeline */}
                    <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                        <h3 className="text-sm font-bold text-white">Activity Timeline</h3>
                        <div className="space-y-3">
                            {customer.activities?.map((act: any) => (
                                <div key={act.id} className="p-3 rounded-xl bg-slate-950/60 border border-slate-800 text-xs space-y-1">
                                    <div className="flex items-center justify-between text-slate-400 font-mono text-[10px]">
                                        <span>{act.activity_type}</span>
                                        <span>{new Date(act.created_at).toLocaleDateString()}</span>
                                    </div>
                                    <p className="text-slate-200 font-medium">{act.description}</p>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};
