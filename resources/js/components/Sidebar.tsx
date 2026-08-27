import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import {
    LayoutDashboard,
    Users,
    PhoneCall,
    Bot,
    Calendar,
    Megaphone,
    BookOpen,
    UserCheck,
    BarChart3,
    Settings,
    Headphones,
    Sparkles,
} from 'lucide-react';

interface SidebarProps {
    onOpenCallSimulator: () => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ onOpenCallSimulator }) => {
    const location = useLocation();

    const menuItems = [
        { name: 'Dashboard', path: '/', icon: LayoutDashboard },
        { name: 'Customers', path: '/customers', icon: Users },
        { name: 'AI Calls', path: '/calls', icon: PhoneCall },
        { name: 'AI Agents', path: '/ai-agents', icon: Bot },
        { name: 'Appointments', path: '/appointments', icon: Calendar },
        // { name: 'Campaigns', path: '/campaigns', icon: Megaphone },
        // { name: 'Knowledge Base', path: '/knowledge-base', icon: BookOpen },
        { name: 'Team Staff', path: '/employees', icon: UserCheck },
        // { name: 'Reports', path: '/reports', icon: BarChart3 },
        { name: 'Settings', path: '/settings', icon: Settings },
    ];

    return (
        <aside className="w-64 bg-slate-900/90 backdrop-blur-2xl border-r border-slate-800/80 flex flex-col shrink-0 select-none shadow-2xl z-20">
            {/* Header / Brand */}
            <div className="h-16 px-6 flex items-center justify-between border-b border-slate-800/60">
                <Link to="/" className="flex items-center gap-3 group">
                    <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 via-teal-400 to-blue-600 p-0.5 shadow-lg shadow-cyan-500/25 group-hover:shadow-cyan-500/40 transition-all duration-300">
                        <div className="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center">
                            <Headphones className="w-5 h-5 text-cyan-400 group-hover:scale-110 transition-transform" />
                        </div>
                    </div>
                    <div>
                        <span className="font-extrabold text-white tracking-wide text-lg flex items-center gap-1">
                            Voice<span className="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-teal-300">AI</span>
                        </span>
                        <span className="block text-[9px] text-cyan-400/80 font-bold tracking-widest uppercase">Calling SaaS</span>
                    </div>
                </Link>
            </div>

            {/* Quick Action Button */}
            <div className="p-4">
                <button
                    onClick={onOpenCallSimulator}
                    className="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-cyan-500 via-teal-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs uppercase tracking-wider shadow-xl shadow-cyan-500/25 hover:shadow-cyan-500/40 transition-all duration-300 flex items-center justify-center gap-2 group cursor-pointer active:scale-95 border border-cyan-400/30"
                >
                    <Sparkles className="w-4 h-4 text-cyan-200 group-hover:rotate-12 transition-transform animate-pulse" />
                    <span>Start AI Call</span>
                </button>
            </div>

            {/* Nav Menu */}
            <nav className="flex-1 px-3.5 py-2 space-y-1.5 overflow-y-auto">
                <div className="px-3 pb-2 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Main Menu</div>
                {menuItems.map((item) => {
                    const Icon = item.icon;
                    const isActive = location.pathname === item.path || (item.path !== '/' && location.pathname.startsWith(item.path));
                    return (
                        <Link
                            key={item.name}
                            to={item.path}
                            className={`flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-200 ${
                                isActive
                                    ? 'bg-gradient-to-r from-cyan-500/15 to-teal-500/10 text-cyan-300 border border-cyan-500/30 shadow-md shadow-cyan-500/10 translate-x-1'
                                    : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60 hover:translate-x-1'
                            }`}
                        >
                            <Icon className={`w-4 h-4 transition-colors ${isActive ? 'text-cyan-400' : 'text-slate-400 group-hover:text-slate-200'}`} />
                            <span>{item.name}</span>
                        </Link>
                    );
                })}
            </nav>

            {/* Active Agent Status Card */}
            <div className="p-4 border-t border-slate-800/80 bg-slate-950/40">
                <div className="p-3 rounded-xl bg-slate-900/80 border border-slate-800 flex items-center gap-3 shadow-inner">
                    <div className="relative">
                        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500/20 to-teal-500/20 flex items-center justify-center text-cyan-300 font-bold text-xs border border-cyan-500/30">
                            AI
                        </div>
                        <span className="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-slate-900 animate-ping"></span>
                        <span className="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-slate-900"></span>
                    </div>
                    <div className="flex-1 min-w-0">
                        <p className="text-xs font-bold text-slate-100 truncate">ABC Voice Agent</p>
                        <p className="text-[10px] text-emerald-400 font-semibold tracking-wide flex items-center gap-1">
                            <span className="w-1 h-1 rounded-full bg-emerald-400"></span> Ready & Active
                        </p>
                    </div>
                </div>
            </div>
        </aside>
    );
};
