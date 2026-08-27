import React, { useState, useEffect } from 'react';
import { Bell, Search, User as UserIcon, CheckCircle2, PhoneCall, Calendar } from 'lucide-react';
import api from '../api/client';

interface TopbarProps {
    user: any;
    onOpenCallSimulator: () => void;
}

export const Topbar: React.FC<TopbarProps> = ({ user, onOpenCallSimulator }) => {
    const [notifications, setNotifications] = useState<any[]>([]);
    const [showNotifications, setShowNotifications] = useState(false);

    useEffect(() => {
        api.get('/notifications').then((res) => {
            if (res.data.success) {
                setNotifications(res.data.data);
            }
        }).catch(() => {});
    }, []);

    const unreadCount = notifications.filter(n => !n.is_read).length;

    return (
        <header className="h-16 bg-slate-900/85 backdrop-blur-2xl border-b border-slate-800/80 px-6 flex items-center justify-between shrink-0 shadow-lg z-10">
            {/* Search Input */}
            <div className="flex items-center gap-3 w-96">
                <div className="relative w-full">
                    <Search className="w-4 h-4 text-cyan-400/70 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        placeholder="Search customers, phone numbers, calls..."
                        className="w-full bg-slate-950/90 border border-slate-800/90 rounded-xl pl-10 pr-4 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-cyan-500/60 focus:ring-2 focus:ring-cyan-500/20 transition-all duration-200"
                    />
                </div>
            </div>

            {/* Right Tools & User */}
            <div className="flex items-center gap-4">
                {/* Immediate AI Call Button */}
                <button
                    onClick={onOpenCallSimulator}
                    className="px-3.5 py-2 rounded-xl bg-gradient-to-r from-cyan-500/15 via-teal-500/10 to-cyan-500/15 hover:from-cyan-500/25 hover:to-teal-500/25 text-cyan-300 border border-cyan-500/35 text-xs font-semibold flex items-center gap-2 transition-all duration-200 cursor-pointer shadow-sm hover:shadow-cyan-500/10"
                >
                    <PhoneCall className="w-3.5 h-3.5 text-cyan-400 animate-pulse" />
                    <span>Quick AI Call</span>
                </button>

                {/* Notifications Dropdown */}
                <div className="relative">
                    <button
                        onClick={() => setShowNotifications(!showNotifications)}
                        className="p-2.5 rounded-xl bg-slate-800/50 hover:bg-slate-800 text-slate-300 border border-slate-700/50 relative transition-all duration-200 cursor-pointer"
                    >
                        <Bell className="w-4 h-4" />
                        {unreadCount > 0 && (
                            <span className="absolute top-1.5 right-1.5 w-2.5 h-2.5 rounded-full bg-cyan-400 animate-ping"></span>
                        )}
                    </button>

                    {showNotifications && (
                        <div className="absolute right-0 mt-2 w-80 bg-slate-900/95 backdrop-blur-2xl border border-slate-800/90 rounded-2xl shadow-2xl z-50 p-4">
                            <div className="flex items-center justify-between pb-3 border-b border-slate-800">
                                <h4 className="text-xs font-bold text-white uppercase tracking-wider">Notifications</h4>
                                <span className="text-[10px] text-cyan-400 font-semibold">{notifications.length} Recent</span>
                            </div>
                            <div className="mt-3 space-y-2.5 max-h-64 overflow-y-auto pr-1">
                                {notifications.length === 0 ? (
                                    <p className="text-xs text-slate-500 text-center py-4">No new notifications</p>
                                ) : (
                                    notifications.map((n) => (
                                        <div key={n.id} className="p-2.5 rounded-xl bg-slate-950/70 border border-slate-800/80 flex items-start gap-2.5">
                                            <Calendar className="w-4 h-4 text-cyan-400 mt-0.5 shrink-0" />
                                            <div>
                                                <p className="text-xs font-semibold text-white">{n.title}</p>
                                                <p className="text-[11px] text-slate-400 mt-0.5">{n.message}</p>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    )}
                </div>

                {/* User Avatar */}
                <div className="flex items-center gap-3 pl-3 border-l border-slate-800/80">
                    <div className="w-9 h-9 rounded-full bg-gradient-to-br from-cyan-400 via-teal-400 to-blue-600 p-0.5 shadow-md shadow-cyan-500/20">
                        <div className="w-full h-full bg-slate-950 rounded-full flex items-center justify-center">
                            <UserIcon className="w-4 h-4 text-cyan-400" />
                        </div>
                    </div>
                    <div className="text-left">
                        <p className="text-xs font-extrabold text-white leading-tight">{user?.name || 'Dr. Vikram Seth'}</p>
                        <p className="text-[10px] text-cyan-400/90 font-semibold leading-tight">{user?.role || 'Super Admin'}</p>
                    </div>
                </div>
            </div>
        </header>
    );
};
