import React, { useState, useEffect } from 'react';
import { Calendar as CalendarIcon, Clock, CheckCircle2, User, Sparkles, Filter } from 'lucide-react';
import api from '../api/client';

export const Appointments: React.FC = () => {
    const [appointments, setAppointments] = useState<any[]>([]);

    useEffect(() => {
        api.get('/appointments').then((res) => {
            if (res.data.success) {
                setAppointments(res.data.data);
            }
        }).catch(() => {});
    }, []);

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-xl font-bold text-white">Booked Appointments</h1>
                    <p className="text-xs text-slate-400">View real-time slots booked automatically by AI Voice Agent and staff</p>
                </div>
            </div>

            <div className="rounded-3xl bg-slate-900/90 border border-slate-800 overflow-hidden">
                <table className="w-full text-left border-collapse">
                    <thead>
                        <tr className="border-b border-slate-800 bg-slate-950/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th className="py-4 px-6">Customer</th>
                            <th className="py-4 px-6">Service</th>
                            <th className="py-4 px-6">Date & Time</th>
                            <th className="py-4 px-6">Booking Source</th>
                            <th className="py-4 px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800/60 text-xs">
                        {appointments.map((app) => (
                            <tr key={app.id} className="hover:bg-slate-800/30 transition-colors">
                                <td className="py-4 px-6 font-bold text-white">
                                    <div>{app.customer?.name}</div>
                                    <div className="text-[11px] text-slate-400">{app.customer?.phone}</div>
                                </td>
                                <td className="py-4 px-6 text-slate-200">
                                    {app.service?.name || 'Weight Loss Consultation'}
                                </td>
                                <td className="py-4 px-6 text-cyan-400 font-semibold">
                                    {app.date} @ {app.start_time}
                                </td>
                                <td className="py-4 px-6">
                                    <span className="px-2.5 py-1 rounded-md text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                        {app.booking_source}
                                    </span>
                                </td>
                                <td className="py-4 px-6">
                                    <span className="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        {app.status}
                                    </span>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};
