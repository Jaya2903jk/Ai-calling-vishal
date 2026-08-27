import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { Sidebar } from './components/Sidebar';
import { Topbar } from './components/Topbar';
import { LiveCallSimulatorModal } from './components/LiveCallSimulatorModal';

import { Dashboard } from './pages/Dashboard';
import { Customers } from './pages/Customers';
import { CustomerDetail } from './pages/CustomerDetail';
import { Calls } from './pages/Calls';
import { CallDetail } from './pages/CallDetail';
import { AIAgents } from './pages/AIAgents';
import { Appointments } from './pages/Appointments';
import { Campaigns } from './pages/Campaigns';
import { Employees } from './pages/Employees';
import { KnowledgeBase } from './pages/KnowledgeBase';
import { Reports } from './pages/Reports';
import { Settings } from './pages/Settings';
import { Login } from './pages/Login';
import api from './api/client';

export const App: React.FC = () => {
    const [user, setUser] = useState<any>({
        name: 'Dr. Vikram Seth',
        email: 'admin@abcwellness.com',
        role: 'Super Admin',
    });
    const [isAuthenticated, setIsAuthenticated] = useState<boolean>(true);
    const [isSimulatorOpen, setIsSimulatorOpen] = useState<boolean>(false);

    useEffect(() => {
        const token = localStorage.getItem('voice_ai_token');
        if (token) {
            api.get('/auth/me')
                .then((res) => {
                    if (res.data.success) {
                        setUser(res.data.user);
                        setIsAuthenticated(true);
                    }
                })
                .catch(() => {});
        }
    }, []);

    const handleLoginSuccess = (userData: any, token: string) => {
        setUser(userData);
        setIsAuthenticated(true);
    };

    if (!isAuthenticated) {
        return <Login onLoginSuccess={handleLoginSuccess} />;
    }

    return (
        <Router>
            <div className="flex h-screen bg-slate-950 text-slate-100 overflow-hidden relative font-sans selection:bg-cyan-500 selection:text-white">
                {/* Background Ambient Glows */}
                <div className="absolute top-0 left-1/4 w-[500px] h-[500px] bg-cyan-500/10 rounded-full blur-[120px] pointer-events-none -z-0"></div>
                <div className="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none -z-0"></div>

                {/* Sidebar Navigation */}
                <Sidebar onOpenCallSimulator={() => setIsSimulatorOpen(true)} />

                {/* Main Content Area */}
                <div className="flex-1 flex flex-col min-w-0 overflow-hidden relative z-10">
                    <Topbar user={user} onOpenCallSimulator={() => setIsSimulatorOpen(true)} />

                    <main className="flex-1 overflow-y-auto p-8 custom-scrollbar">
                        <Routes>
                            <Route path="/" element={<Dashboard onOpenCallSimulator={() => setIsSimulatorOpen(true)} />} />
                            <Route path="/customers" element={<Customers onOpenCallSimulator={() => setIsSimulatorOpen(true)} />} />
                            <Route path="/customers/:id" element={<CustomerDetail />} />
                            <Route path="/calls" element={<Calls onOpenCallSimulator={() => setIsSimulatorOpen(true)} />} />
                            <Route path="/calls/:id" element={<CallDetail />} />
                            <Route path="/ai-agents" element={<AIAgents />} />
                            <Route path="/appointments" element={<Appointments />} />
                            <Route path="/campaigns" element={<Campaigns />} />
                            <Route path="/knowledge-base" element={<KnowledgeBase />} />
                            <Route path="/employees" element={<Employees />} />
                            <Route path="/reports" element={<Reports />} />
                            <Route path="/settings" element={<Settings />} />
                            <Route path="*" element={<Navigate to="/" replace />} />
                        </Routes>
                    </main>
                </div>

                {/* Interactive AI Call Launcher Modal */}
                <LiveCallSimulatorModal
                    isOpen={isSimulatorOpen}
                    onClose={() => setIsSimulatorOpen(false)}
                    onCallCompleted={() => {
                        // Refresh active page data if needed
                    }}
                />
            </div>
        </Router>
    );
};

export default App;
