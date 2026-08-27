import React, { useState, useEffect } from 'react';
import { Megaphone, Play, Pause, Square, Plus, CheckCircle2, Users } from 'lucide-react';
import api from '../api/client';

export const Campaigns: React.FC = () => {
    const [campaigns, setCampaigns] = useState<any[]>([]);

    useEffect(() => {
        api.get('/campaigns').then((res) => {
            if (res.data.success) {
                setCampaigns(res.data.data);
            }
        }).catch(() => {});
    }, []);

    const handleStart = async (id: number) => {
        await api.post(`/campaigns/${id}/start`);
        api.get('/campaigns').then((res) => setCampaigns(res.data.data));
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-xl font-bold text-white">Bulk AI Calling Campaigns</h1>
                    <p className="text-xs text-slate-400">Automated queue dispatching & batch call outreach with rate limiting</p>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {campaigns.map((camp) => (
                    <div key={camp.id} className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-base font-extrabold text-white">{camp.name}</h3>
                                <p className="text-xs text-slate-400">{camp.call_purpose}</p>
                            </div>
                            <span className="px-2.5 py-1 rounded-md text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                {camp.status}
                            </span>
                        </div>

                        <div className="grid grid-cols-3 gap-2 py-2 border-y border-slate-800 text-center">
                            <div>
                                <span className="text-slate-500 block text-[10px] uppercase font-bold">Total</span>
                                <span className="font-extrabold text-white text-base">{camp.total_contacts}</span>
                            </div>
                            <div>
                                <span className="text-slate-500 block text-[10px] uppercase font-bold">Completed</span>
                                <span className="font-extrabold text-emerald-400 text-base">{camp.completed_calls}</span>
                            </div>
                            <div>
                                <span className="text-slate-500 block text-[10px] uppercase font-bold">Bookings</span>
                                <span className="font-extrabold text-cyan-400 text-base">{camp.conversions}</span>
                            </div>
                        </div>

                        <button
                            onClick={() => handleStart(camp.id)}
                            className="w-full py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs shadow-lg shadow-cyan-500/25 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <Play className="w-4 h-4 fill-white" />
                            <span>Start Campaign Processing</span>
                        </button>
                    </div>
                ))}
            </div>
        </div>
    );
};
