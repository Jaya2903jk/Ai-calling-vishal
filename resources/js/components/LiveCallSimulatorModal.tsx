import React, { useState, useEffect, useRef } from 'react';
import { X, PhoneCall, Mic, Sparkles, CheckCircle2, Calendar, Clock, Bot, User, Volume2, ShieldCheck, Send, PhoneOff } from 'lucide-react';
import api from '../api/client';

interface LiveCallSimulatorModalProps {
    isOpen: boolean;
    onClose: () => void;
    onCallCompleted?: () => void;
}

export const LiveCallSimulatorModal: React.FC<LiveCallSimulatorModalProps> = ({ isOpen, onClose, onCallCompleted }) => {
    const [customers, setCustomers] = useState<any[]>([]);
    const [agents, setAgents] = useState<any[]>([]);
    const [selectedCustomerId, setSelectedCustomerId] = useState<string>('');
    const [selectedAgentId, setSelectedAgentId] = useState<string>('');
    const [callPurpose, setCallPurpose] = useState<string>('Weight Loss Consultation Follow-Up');

    const [isCalling, setIsCalling] = useState<boolean>(false);
    const [callData, setCallData] = useState<any>(null);
    const [callStatus, setCallStatus] = useState<string>('idle'); // idle, dialing, connected, completed
    const [customerInput, setCustomerInput] = useState<string>('');
    const [isListening, setIsListening] = useState<boolean>(false);
    const [secondsElapsed, setSecondsElapsed] = useState<number>(0);

    const timerRef = useRef<any>(null);
    const dialogueEndRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (isOpen) {
            api.get('/customers').then((res) => {
                if (res.data.success && res.data.data.data) {
                    setCustomers(res.data.data.data);
                    if (res.data.data.data.length > 0) {
                        setSelectedCustomerId(res.data.data.data[0].id.toString());
                    }
                }
            }).catch(() => {});

            api.get('/ai-agents').then((res) => {
                if (res.data.success && res.data.data.length > 0) {
                    setAgents(res.data.data);
                    setSelectedAgentId(res.data.data[0].id.toString());
                }
            }).catch(() => {});
        } else {
            resetCallState();
        }
    }, [isOpen]);

    useEffect(() => {
        if (callStatus === 'connected') {
            timerRef.current = setInterval(() => {
                setSecondsElapsed((prev) => prev + 1);
            }, 1000);
        } else {
            if (timerRef.current) clearInterval(timerRef.current);
        }
        return () => {
            if (timerRef.current) clearInterval(timerRef.current);
        };
    }, [callStatus]);

    useEffect(() => {
        dialogueEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [callData?.transcript?.dialogue_json]);

    const resetCallState = () => {
        setCallStatus('idle');
        setIsCalling(false);
        setCallData(null);
        setSecondsElapsed(0);
        setCustomerInput('');
        if (timerRef.current) clearInterval(timerRef.current);
    };

    const speakAIResponse = (text: string) => {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            window.speechSynthesis.speak(utterance);
        }
    };

    const handleStartInteractiveCall = async () => {
        setIsCalling(true);
        setCallStatus('dialing');

        try {
            const res = await api.post('/calls/start-interactive', {
                customer_id: selectedCustomerId,
                ai_agent_id: selectedAgentId,
                call_purpose: callPurpose,
            });

            if (res.data.success) {
                setCallData(res.data.call);
                setCallStatus('connected');
                setIsCalling(false);
                if (res.data.ai_greeting) {
                    speakAIResponse(res.data.ai_greeting);
                }
            }
        } catch (error) {
            console.error(error);
            setIsCalling(false);
            setCallStatus('idle');
        }
    };

    const handleTriggerRealPhoneCall = async () => {
        setIsCalling(true);
        setCallStatus('dialing');

        try {
            const res = await api.post('/calls/trigger', {
                customer_id: selectedCustomerId,
                ai_agent_id: selectedAgentId,
                call_purpose: callPurpose,
            });

            if (res.data.success) {
                setCallData(res.data.call);
                setCallStatus('connected');
                setIsCalling(false);
            }
        } catch (error) {
            console.error(error);
            setIsCalling(false);
            setCallStatus('idle');
        }
    };

    const handleFinalizePhoneCall = async () => {
        if (!callData) return;
        setIsCalling(true);

        try {
            const res = await api.post(`/calls/${callData.id}/finalize`);
            if (res.data.success) {
                setCallData(res.data.call);
                setCallStatus('completed');
                setIsCalling(false);
                if (onCallCompleted) onCallCompleted();
            }
        } catch (error) {
            console.error(error);
            setIsCalling(false);
        }
    };

    const handleSendTurn = async (inputText?: string) => {
        const textToSend = inputText || customerInput;
        if (!textToSend.trim() || !callData) return;

        setCustomerInput('');

        try {
            const res = await api.post('/calls/interactive-turn', {
                call_id: callData.id,
                customer_input: textToSend,
            });

            if (res.data.success) {
                setCallData(res.data.call);
                if (res.data.ai_response) {
                    speakAIResponse(res.data.ai_response);
                }

                if (res.data.status === 'completed' || res.data.action_taken === 'CONFIRMED_APPOINTMENT') {
                    setCallStatus('completed');
                    if (onCallCompleted) onCallCompleted();
                }
            }
        } catch (error) {
            console.error(error);
        }
    };

    const toggleSpeechRecognition = () => {
        const SpeechRecognition = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;
        if (!SpeechRecognition) {
            alert('Web Speech API is not supported in this browser. You can type your response in real-time!');
            return;
        }

        if (isListening) {
            setIsListening(false);
            return;
        }

        const recognition = new SpeechRecognition();
        recognition.lang = 'en-US';
        recognition.interimResults = false;

        recognition.onstart = () => setIsListening(true);
        recognition.onend = () => setIsListening(false);
        recognition.onresult = (event: any) => {
            const transcript = event.results[0][0].transcript;
            setCustomerInput(transcript);
            handleSendTurn(transcript);
        };

        recognition.start();
    };

    const formatTimer = (secs: number) => {
        const mins = Math.floor(secs / 60);
        const remSecs = secs % 60;
        return `${mins < 10 ? '0' : ''}${mins}:${remSecs < 10 ? '0' : ''}${remSecs}`;
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-4">
            <div className="bg-slate-900 border border-slate-800 rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl shadow-cyan-500/10 flex flex-col max-h-[90vh]">
                {/* Top Header */}
                <div className="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
                    <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 p-0.5 shadow-md shadow-cyan-500/20">
                            <div className="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center">
                                <Bot className="w-5 h-5 text-cyan-400" />
                            </div>
                        </div>
                        <div>
                            <h3 className="text-base font-bold text-white flex items-center gap-2">
                                Real-Time AI Voice Call Engine
                                {callStatus === 'connected' && (
                                    <span className="px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] uppercase font-bold flex items-center gap-1.5 animate-pulse">
                                        <span className="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        CONNECTED • {formatTimer(secondsElapsed)}
                                    </span>
                                )}
                                {callStatus === 'completed' && (
                                    <span className="px-2 py-0.5 rounded-md bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 text-[10px] uppercase font-bold">
                                        CALL ENDED
                                    </span>
                                )}
                            </h3>
                            <p className="text-xs text-slate-400">Live Speech Synthesis & Intent Resolution</p>
                        </div>
                    </div>
                    <button
                        onClick={() => {
                            if ('speechSynthesis' in window) window.speechSynthesis.cancel();
                            onClose();
                        }}
                        className="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-all cursor-pointer"
                    >
                        <X className="w-5 h-5" />
                    </button>
                </div>

                {/* Main Interactive Content */}
                <div className="p-6 overflow-y-auto space-y-5 flex-1">
                    {callStatus === 'idle' && (
                        <div className="space-y-4">
                            <div>
                                <label className="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Select Customer to Call
                                </label>
                                <select
                                    value={selectedCustomerId}
                                    onChange={(e) => setSelectedCustomerId(e.target.value)}
                                    className="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-cyan-500/50"
                                >
                                    {customers.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.name} — {c.phone} ({c.service?.name || 'Weight Loss Consultation'})
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">
                                    AI Voice Agent
                                </label>
                                <select
                                    value={selectedAgentId}
                                    onChange={(e) => setSelectedAgentId(e.target.value)}
                                    className="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-cyan-500/50"
                                >
                                    {agents.map((a) => (
                                        <option key={a.id} value={a.id}>
                                            {a.name} ({a.language})
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-xs font-bold text-slate-300 mb-1.5 uppercase tracking-wider">
                                    Call Purpose
                                </label>
                                <input
                                    type="text"
                                    value={callPurpose}
                                    onChange={(e) => setCallPurpose(e.target.value)}
                                    className="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-cyan-500/50"
                                />
                            </div>

                            <div className="p-4 rounded-2xl bg-cyan-500/5 border border-cyan-500/20 flex items-start gap-3">
                                <ShieldCheck className="w-5 h-5 text-cyan-400 shrink-0 mt-0.5" />
                                <div>
                                    <h4 className="text-xs font-bold text-cyan-400">Real-Time Interactive Call Mode</h4>
                                    <p className="text-xs text-slate-400 mt-0.5">
                                        Starts a live session. You can speak into your microphone or type responses in real-time. The AI will respond with live speech audio and automatically lock appointment slots upon confirmation.
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {callStatus === 'dialing' && (
                        <div className="py-12 flex flex-col items-center justify-center text-center space-y-4">
                            <div className="relative">
                                <div className="w-20 h-20 rounded-full bg-cyan-500/20 flex items-center justify-center text-cyan-400 border border-cyan-500/30 animate-pulse">
                                    <PhoneCall className="w-8 h-8 text-cyan-400 animate-bounce" />
                                </div>
                                <span className="absolute top-0 right-0 w-4 h-4 rounded-full bg-emerald-400 border-2 border-slate-900 animate-ping"></span>
                            </div>
                            <div>
                                <h4 className="text-base font-bold text-white">Dialing Customer & Connecting Voice Stream...</h4>
                                <p className="text-xs text-slate-400 mt-1">Initializing AI Speech Engine & Real-time WebSockets...</p>
                            </div>
                        </div>
                    )}

                    {(callStatus === 'connected' || callStatus === 'completed') && callData && (
                        <div className="space-y-4">
                            {/* Call Status Banner */}
                            {callStatus === 'completed' && (
                                <div className="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center gap-3">
                                    <CheckCircle2 className="w-6 h-6 text-emerald-400 shrink-0" />
                                    <div>
                                        <h4 className="text-sm font-bold text-white">Appointment Confirmed & Call Completed!</h4>
                                        <p className="text-xs text-emerald-300 mt-0.5">
                                            Appointment locked in database. Call duration: {formatTimer(secondsElapsed || callData.duration_seconds || 35)}.
                                        </p>
                                    </div>
                                </div>
                            )}

                            {/* Live Speech Transcript Stream */}
                            <div className="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3 max-h-72 overflow-y-auto">
                                <h5 className="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800 pb-2 flex items-center justify-between">
                                    <span className="flex items-center gap-2"><Sparkles className="w-4 h-4 text-cyan-400" /> Live Speech Transcript</span>
                                    <span className="text-[10px] text-emerald-400 font-mono">Speaker Labels Active</span>
                                </h5>

                                <div className="space-y-3">
                                    {callData.transcript?.dialogue_json?.map((turn: any, idx: number) => (
                                        <div key={idx} className={`flex items-start gap-2.5 ${turn.speaker === 'AI' ? 'pl-1' : 'pr-1'}`}>
                                            <div
                                                className={`w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5 ${
                                                    turn.speaker === 'AI'
                                                        ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30'
                                                        : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                                                }`}
                                            >
                                                {turn.speaker === 'AI' ? 'AI' : 'C'}
                                            </div>
                                            <div className="flex-1 bg-slate-900/80 p-2.5 rounded-xl border border-slate-800/80">
                                                <div className="flex items-center justify-between mb-1">
                                                    <span className="text-[11px] font-bold text-white">
                                                        {turn.speaker === 'AI' ? callData.ai_agent?.name || 'ABC Wellness AI Assistant' : callData.customer?.name}
                                                    </span>
                                                    <span className="text-[10px] font-mono text-slate-500">{turn.timestamp}</span>
                                                </div>
                                                <p className="text-xs text-slate-300 leading-relaxed">{turn.text}</p>
                                            </div>
                                        </div>
                                    ))}
                                    <div ref={dialogueEndRef} />
                                </div>
                            </div>

                            {/* Quick Test Prompt Shortcuts */}
                            {callStatus === 'connected' && (
                                <div className="space-y-2">
                                    <span className="text-[11px] font-bold text-slate-400 block uppercase tracking-wider">Quick Response Options:</span>
                                    <div className="flex flex-wrap gap-2">
                                        <button
                                            onClick={() => handleSendTurn("Yes, I am interested in booking a consultation.")}
                                            className="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-cyan-300 border border-cyan-500/30 cursor-pointer transition-all"
                                        >
                                            "Yes, I want to book a consultation"
                                        </button>
                                        <button
                                            onClick={() => handleSendTurn("Saturday evening works for me.")}
                                            className="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-cyan-300 border border-cyan-500/30 cursor-pointer transition-all"
                                        >
                                            "Saturday evening works for me"
                                        </button>
                                        <button
                                            onClick={() => handleSendTurn("6 PM.")}
                                            className="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-emerald-300 border border-emerald-500/30 cursor-pointer transition-all"
                                        >
                                            "6 PM (Confirm Slot)"
                                        </button>
                                        <button
                                            onClick={() => handleSendTurn("What is the consultation price?")}
                                            className="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 border border-slate-700 cursor-pointer transition-all"
                                        >
                                            "What is the price?"
                                        </button>
                                    </div>
                                </div>
                            )}

                            {/* Live Speech/Text Input Controls */}
                            {callStatus === 'connected' && (
                                <div className="flex items-center gap-2 pt-2">
                                    <button
                                        onClick={toggleSpeechRecognition}
                                        className={`p-3 rounded-xl border transition-all cursor-pointer shrink-0 ${
                                            isListening
                                                ? 'bg-red-500/20 text-red-400 border-red-500/40 animate-pulse'
                                                : 'bg-slate-800 hover:bg-slate-700 text-cyan-400 border-slate-700'
                                        }`}
                                        title={isListening ? 'Stop Listening' : 'Speak via Microphone'}
                                    >
                                        <Mic className="w-5 h-5" />
                                    </button>
                                    <input
                                        type="text"
                                        value={customerInput}
                                        onChange={(e) => setCustomerInput(e.target.value)}
                                        onKeyDown={(e) => e.key === 'Enter' && handleSendTurn()}
                                        placeholder="Type or speak customer response..."
                                        className="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-cyan-500/50"
                                    />
                                    <button
                                        onClick={() => handleSendTurn()}
                                        className="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-lg shadow-cyan-500/25 flex items-center gap-1.5 cursor-pointer"
                                    >
                                        <Send className="w-3.5 h-3.5" />
                                        <span>Send</span>
                                    </button>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* Footer Controls */}
                <div className="p-4 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
                    {callStatus === 'idle' ? (
                        <div className="flex items-center justify-between w-full gap-3">
                            <button
                                onClick={onClose}
                                className="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-slate-200 transition-all cursor-pointer"
                            >
                                Cancel
                            </button>
                            <div className="flex items-center gap-2">
                                <button
                                    onClick={handleTriggerRealPhoneCall}
                                    className="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-2 cursor-pointer"
                                >
                                    <PhoneCall className="w-4 h-4" />
                                    <span>Dial Real Mobile Phone (+918825816433)</span>
                                </button>
                                <button
                                    onClick={handleStartInteractiveCall}
                                    className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-semibold text-xs shadow-lg shadow-cyan-500/25 flex items-center gap-2 cursor-pointer"
                                >
                                    <Sparkles className="w-4 h-4" />
                                    <span>Start Live Real-Time Call</span>
                                </button>
                            </div>
                        </div>
                    ) : callStatus === 'connected' ? (
                        <div className="flex items-center justify-between w-full">
                            <span className="text-xs text-slate-400 font-mono flex items-center gap-2">
                                <span className="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                Live Voice Call Active
                            </span>
                            <button
                                onClick={handleFinalizePhoneCall}
                                className="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/40 text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-lg shadow-emerald-500/10"
                            >
                                <PhoneOff className="w-3.5 h-3.5" />
                                <span>End Call & Sync Summary</span>
                            </button>
                        </div>
                    ) : (
                        <div className="flex items-center justify-end w-full gap-3">
                            <button
                                onClick={() => {
                                    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
                                    onClose();
                                }}
                                className="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-lg shadow-cyan-500/25 cursor-pointer"
                            >
                                Done & View Call Record
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

