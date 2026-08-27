import React, { useState, useEffect } from 'react';
import { UserCheck, Shield, Plus } from 'lucide-react';
import api from '../api/client';

export const Employees: React.FC = () => {
    const [employees, setEmployees] = useState<any[]>([]);

    useEffect(() => {
        api.get('/employees').then((res) => {
            if (res.data.success) {
                setEmployees(res.data.data);
            }
        }).catch(() => {});
    }, []);

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-xl font-bold text-white">Employee & Admin Management</h1>
                <p className="text-xs text-slate-400">Manage staff team members, assigned customers, and role permissions</p>
            </div>

            <div className="rounded-3xl bg-slate-900/90 border border-slate-800 overflow-hidden">
                <table className="w-full text-left border-collapse">
                    <thead>
                        <tr className="border-b border-slate-800 bg-slate-950/50 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th className="py-4 px-6">Name</th>
                            <th className="py-4 px-6">Email & Phone</th>
                            <th className="py-4 px-6">Role</th>
                            <th className="py-4 px-6">Department</th>
                            <th className="py-4 px-6">Status</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800/60 text-xs">
                        {employees.map((emp) => (
                            <tr key={emp.id} className="hover:bg-slate-800/30 transition-colors">
                                <td className="py-4 px-6 font-bold text-white">{emp.name}</td>
                                <td className="py-4 px-6 text-slate-300">
                                    <div>{emp.email}</div>
                                    <div className="text-[11px] text-slate-500">{emp.phone}</div>
                                </td>
                                <td className="py-4 px-6 font-semibold text-cyan-400">
                                    {emp.role?.name || 'Employee'}
                                </td>
                                <td className="py-4 px-6 text-slate-300">{emp.department || 'Client Care'}</td>
                                <td className="py-4 px-6">
                                    <span className="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        {emp.status}
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
