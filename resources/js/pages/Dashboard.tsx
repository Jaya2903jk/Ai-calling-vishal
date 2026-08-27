import React, { useState, useEffect } from 'react';
import { Users, PhoneCall, CheckCircle2, XCircle, Calendar, TrendingUp, Sparkles, Clock, ArrowUpRight } from 'lucide-react';
import api from '../api/client';
import { Link } from 'react-router-dom';

interface DashboardProps {
    onOpenCallSimulator: () => void;
}

export const Dashboard: React.FC<DashboardProps> = ({ onOpenCallSimulator }) => {
    const [metrics, setMetrics] = useState<any>(null);
    const [charts, setCharts] = useState<any>(null);
    const [recentCalls, setRecentCalls] = useState<any[]>([]);

    useEffect(() => {
        api.get('/dashboard/metrics').then((res) => {
            if (res.data.success) setMetrics(res.data.metrics);
        }).catch(() => {});

        api.get('/dashboard/charts').then((res) => {
            if (res.data.success) setCharts(res.data.charts);
        }).catch(() => {});

        api.get('/calls?per_page=5').then((res) => {
            if (res.data.success) {
                const callArray = Array.isArray(res.data.data)
                    ? res.data.data
                    : (res.data.data?.data || []);
                setRecentCalls(callArray);
            }
        }).catch(() => {});
    }, []);

    const cards = [
        { title: 'Total Customers', value: metrics?.total_customers ?? 5, icon: Users, change: '+12%', color: 'from-blue-500/20 to-cyan-500/20 text-cyan-400' },
        { title: 'Total AI Calls', value: metrics?.total_calls ?? 8, icon: PhoneCall, change: '+24%', color: 'from-cyan-500/20 to-teal-500/20 text-cyan-300' },
        { title: 'Appointments Booked', value: metrics?.appointments_booked ?? 3, icon: Calendar, change: '+18%', color: 'from-emerald-500/20 to-green-500/20 text-emerald-400' },
        { title: 'AI Conversion Rate', value: `${metrics?.ai_conversion_rate ?? 37.5}%`, icon: TrendingUp, change: '+5.4%', color: 'from-indigo-500/20 to-purple-500/20 text-indigo-400' },
    ];

    return (
        <div className="space-y-6">
            {/* Hero / Banner */}
            <div className="p-6 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-900/90 to-cyan-950/40 border border-slate-800 flex items-center justify-between relative overflow-hidden">
                <div className="relative z-10 space-y-1.5 max-w-xl">
                    <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 text-xs font-semibold">
                        <Sparkles className="w-3.5 h-3.5" />
                        <span>AI Calling Assistant Active</span>
                    </div>
                    <h1 className="text-2xl font-extrabold text-white tracking-tight">
                        AI Employee Call & Appointment Dashboard
                    </h1>
                    <p className="text-xs text-slate-400">
                        Automatically call customers, answer FAQs, check real-time availability, and book appointments.
                    </p>
                </div>
                <button
                    onClick={onOpenCallSimulator}
                    className="relative z-10 px-5 py-3 rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs shadow-xl shadow-cyan-500/20 flex items-center gap-2 cursor-pointer transition-transform active:scale-95"
                >
                    <PhoneCall className="w-4 h-4" />
                    <span>Launch AI Call</span>
                </button>
            </div>

            {/* Metrics Cards Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {cards.map((card, idx) => {
                    const Icon = card.icon;
                    return (
                        <div key={idx} className="p-5 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">{card.title}</span>
                                <div className={`w-9 h-9 rounded-xl bg-gradient-to-br ${card.color} flex items-center justify-center border border-slate-800`}>
                                    <Icon className="w-4 h-4" />
                                </div>
                            </div>
                            <div className="flex items-baseline justify-between">
                                <span className="text-2xl font-extrabold text-white">{card.value}</span>
                                <span className="text-[11px] font-semibold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">
                                    {card.change}
                                </span>
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Charts & Recent Calls Section */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Left: Call Outcome & Volume Visuals */}
                <div className="lg:col-span-2 p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                    <div className="flex items-center justify-between border-b border-slate-800 pb-4">
                        <div>
                            <h3 className="text-sm font-bold text-white">Call Volume & Appointment Conversions</h3>
                            <p className="text-xs text-slate-400">Daily AI voice calls initiated vs confirmed appointments</p>
                        </div>
                        <span className="text-xs font-semibold text-cyan-400 bg-cyan-500/10 px-3 py-1 rounded-lg border border-cyan-500/20">
                            Last 7 Days
                        </span>
                    </div>

                    {/* Bar Visualizer */}
                    <div className="h-48 flex items-end justify-between gap-4 pt-6 px-4">
                        {charts?.labels?.map((label: string, idx: number) => {
                            const calls = charts?.calls_per_day?.[idx] ?? 2;
                            const appts = charts?.appointments_per_day?.[idx] ?? 1;
                            const heightCalls = Math.max(20, calls * 35);
                            const heightAppts = Math.max(15, appts * 35);

                            return (
                                <div key={idx} className="flex-1 flex flex-col items-center gap-2">
                                    <div className="w-full flex items-end justify-center gap-1.5 h-36">
                                        <div
                                            style={{ height: `${heightCalls}%` }}
                                            className="w-3 rounded-t-md bg-cyan-500/60 hover:bg-cyan-400 transition-all"
                                            title={`Calls: ${calls}`}
                                        />
                                        <div
                                            style={{ height: `${heightAppts}%` }}
                                            className="w-3 rounded-t-md bg-emerald-400 hover:bg-emerald-300 transition-all"
                                            title={`Appointments: ${appts}`}
                                        />
                                    </div>
                                    <span className="text-[10px] font-semibold text-slate-400">{label}</span>
                                </div>
                            );
                        })}
                    </div>

                    <div className="flex items-center justify-center gap-6 pt-2 border-t border-slate-800/60 text-xs">
                        <div className="flex items-center gap-2">
                            <span className="w-3 h-3 rounded-sm bg-cyan-500/60"></span>
                            <span className="text-slate-400 font-medium">AI Calls</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="w-3 h-3 rounded-sm bg-emerald-400"></span>
                            <span className="text-slate-400 font-medium">Confirmed Appointments</span>
                        </div>
                    </div>
                </div>

                {/* Right: Recent Calls Feed */}
                <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                    <div className="flex items-center justify-between border-b border-slate-800 pb-4">
                        <h3 className="text-sm font-bold text-white">Recent AI Calls</h3>
                        <Link to="/calls" className="text-xs font-semibold text-cyan-400 hover:underline flex items-center gap-1">
                            View All <ArrowUpRight className="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <div className="space-y-3">
                        {recentCalls.map((call) => (
                            <Link
                                key={call.id}
                                to={`/calls/${call.id}`}
                                className="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 hover:border-cyan-500/30 flex items-center justify-between transition-all block group"
                            >
                                <div className="space-y-0.5">
                                    <p className="text-xs font-bold text-white group-hover:text-cyan-400 transition-colors">
                                        {call.customer?.name || 'Customer'}
                                    </p>
                                    <p className="text-[11px] text-slate-400">{call.call_purpose}</p>
                                </div>
                                <span className={`px-2 py-0.5 rounded-md text-[10px] font-bold ${
                                    call.status === 'completed' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                                }`}>
                                    {call.status}
                                </span>
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};
