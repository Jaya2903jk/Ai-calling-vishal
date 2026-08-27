import React, { useState, useEffect } from 'react';
import { Users, PhoneCall, Search, Plus, Filter, ArrowRight, UserCheck } from 'lucide-react';
import { Link } from 'react-router-dom';
import api from '../api/client';

interface CustomersProps {
    onOpenCallSimulator: () => void;
}

export const Customers: React.FC<CustomersProps> = ({ onOpenCallSimulator }) => {
    const [customers, setCustomers] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('');

    const fetchCustomers = () => {
        setLoading(true);
        api.get(`/customers?search=${search}&status=${statusFilter}`)
            .then((res) => {
                if (res.data.success && res.data.data.data) {
                    setCustomers(res.data.data.data);
                }
                setLoading(false);
            })
            .catch(() => setLoading(false));
    };

    useEffect(() => {
        fetchCustomers();
    }, [search, statusFilter]);

    return (
        <div className="space-y-6">
            {/* Top Bar */}
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-xl font-bold text-white">Customer CRM Directory</h1>
                    <p className="text-xs text-slate-400">Manage leads, phone numbers, lead statuses, and AI call histories</p>
                </div>
                <button
                    onClick={onOpenCallSimulator}
                    className="px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-semibold text-xs shadow-lg shadow-cyan-500/25 flex items-center gap-2 cursor-pointer"
                >
                    <PhoneCall className="w-4 h-4" />
                    <span>AI Call Customer</span>
                </button>
            </div>

            {/* Filter Bar */}
            <div className="p-4 rounded-2xl bg-slate-900/90 border border-slate-800 flex items-center gap-4">
                <div className="relative flex-1">
                    <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        placeholder="Search by customer name, phone number, or email..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-4 py-2 text-xs text-slate-200 focus:outline-none focus:border-cyan-500/50"
                    />
                </div>
                <select
                    value={statusFilter}
                    onChange={(e) => setStatusFilter(e.target.value)}
                    className="bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-200 focus:outline-none"
                >
                    <option value="">All Lead Statuses</option>
                    <option value="New">New</option>
                    <option value="Contacted">Contacted</option>
                    <option value="Interested">Interested</option>
                    <option value="Appointment Booked">Appointment Booked</option>
                    <option value="Converted">Converted</option>
                </select>
            </div>

            {/* Table */}
            <div className="rounded-3xl bg-slate-900/90 border border-slate-800 overflow-hidden">
                <table className="w-full text-left border-collapse">
                    <thead>
                        <tr className="border-b border-slate-800 bg-slate-950/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th className="py-4 px-6">Customer</th>
                            <th className="py-4 px-6">Phone & Email</th>
                            <th className="py-4 px-6">Interested Service</th>
                            <th className="py-4 px-6">Lead Status</th>
                            <th className="py-4 px-6">Assigned Staff</th>
                            <th className="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800/60 text-xs">
                        {customers.map((c) => (
                            <tr key={c.id} className="hover:bg-slate-800/30 transition-colors">
                                <td className="py-4 px-6 font-bold text-white">
                                    <Link to={`/customers/${c.id}`} className="hover:text-cyan-400 transition-colors">
                                        {c.name}
                                    </Link>
                                </td>
                                <td className="py-4 px-6 text-slate-300">
                                    <div>{c.phone}</div>
                                    <div className="text-[11px] text-slate-500">{c.email || 'No email'}</div>
                                </td>
                                <td className="py-4 px-6 font-medium text-slate-300">
                                    {c.service?.name || 'General Enquiry'}
                                </td>
                                <td className="py-4 px-6">
                                    <span className="px-2.5 py-1 rounded-md text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                        {c.lead_status}
                                    </span>
                                </td>
                                <td className="py-4 px-6 text-slate-300">
                                    {c.assigned_employee?.name || 'Priya Sharma'}
                                </td>
                                <td className="py-4 px-6 text-right">
                                    <Link
                                        to={`/customers/${c.id}`}
                                        className="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-cyan-500/20 text-slate-200 hover:text-cyan-400 text-xs font-semibold inline-flex items-center gap-1.5 transition-all"
                                    >
                                        <span>View Profile</span>
                                        <ArrowRight className="w-3.5 h-3.5" />
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
