import React, { useState, useEffect } from 'react';
import { PhoneCall, Play, RefreshCw, Eye, Sparkles } from 'lucide-react';
import { Link } from 'react-router-dom';
import api from '../api/client';

interface CallsProps {
    onOpenCallSimulator: () => void;
}

export const Calls: React.FC<CallsProps> = ({ onOpenCallSimulator }) => {
    const [calls, setCalls] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);

    const fetchCalls = () => {
        setLoading(true);
        api.get('/calls')
            .then((res) => {
                if (res.data.success) {
                    const callArray = Array.isArray(res.data.data)
                        ? res.data.data
                        : (res.data.data?.data || []);
                    setCalls(callArray);
                }
                setLoading(false);
            })
            .catch(() => setLoading(false));
    };

    useEffect(() => {
        fetchCalls();
    }, []);

    const handleRetry = async (callId: number) => {
        await api.post(`/calls/${callId}/retry`);
        fetchCalls();
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-xl font-bold text-white">AI Call Logs & Transcripts</h1>
                    <p className="text-xs text-slate-400">Review recordings, speech-to-text transcripts, and AI call classifications</p>
                </div>
                <button
                    onClick={onOpenCallSimulator}
                    className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-semibold text-xs shadow-lg shadow-cyan-500/25 flex items-center gap-2 cursor-pointer"
                >
                    <Sparkles className="w-4 h-4" />
                    <span>Start New AI Call</span>
                </button>
            </div>

            {/* Table */}
            <div className="rounded-3xl bg-slate-900/90 border border-slate-800 overflow-hidden">
                <table className="w-full text-left border-collapse">
                    <thead>
                        <tr className="border-b border-slate-800 bg-slate-950/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th className="py-4 px-6">Customer & Phone</th>
                            <th className="py-4 px-6">Call Purpose</th>
                            <th className="py-4 px-6">AI Agent</th>
                            <th className="py-4 px-6">Status</th>
                            <th className="py-4 px-6">Outcome</th>
                            <th className="py-4 px-6">Duration</th>
                            <th className="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800/60 text-xs">
                        {calls.map((c) => (
                            <tr key={c.id} className="hover:bg-slate-800/30 transition-colors">
                                <td className="py-4 px-6 font-bold text-white">
                                    <div>{c.customer?.name}</div>
                                    <div className="text-[11px] text-slate-400">{c.phone_number}</div>
                                </td>
                                <td className="py-4 px-6 font-medium text-slate-300">
                                    {c.call_purpose}
                                </td>
                                <td className="py-4 px-6 text-slate-300">
                                    {c.ai_agent?.name || 'ABC Wellness Assistant'}
                                </td>
                                <td className="py-4 px-6">
                                    <span className="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        {c.status}
                                    </span>
                                </td>
                                <td className="py-4 px-6 font-semibold text-cyan-400">
                                    {c.outcome?.outcome || 'Appointment Booked'}
                                </td>
                                <td className="py-4 px-6 text-slate-400 font-mono">
                                    {c.duration_seconds}s
                                </td>
                                <td className="py-4 px-6 text-right space-x-2">
                                    <button
                                        onClick={() => handleRetry(c.id)}
                                        className="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-all cursor-pointer"
                                        title="Retry Call"
                                    >
                                        <RefreshCw className="w-3.5 h-3.5" />
                                    </button>
                                    <Link
                                        to={`/calls/${c.id}`}
                                        className="px-3 py-1.5 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 text-xs font-semibold inline-flex items-center gap-1.5 transition-all"
                                    >
                                        <Eye className="w-3.5 h-3.5" />
                                        <span>Details</span>
                                    </Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};
