import React, { useState } from 'react';
import { Settings as SettingsIcon, Shield, Database, Phone, Bell, Lock } from 'lucide-react';

export const Settings: React.FC = () => {
    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-xl font-bold text-white">Platform Settings & Integrations</h1>
                <p className="text-xs text-slate-400">Configure Voice Provider abstraction, recording retention, compliance, and security</p>
            </div>

            <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-6">
                <div className="space-y-4">
                    <h3 className="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                        <Phone className="w-4 h-4 text-cyan-400" /> Voice & Telephony Provider Configuration
                    </h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label className="block font-semibold text-slate-300 mb-1">Selected Telephony Driver</label>
                            <select className="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none">
                                <option value="mock">Mock / Development AI Engine (Active)</option>
                                <option value="twilio">Twilio Programmable Voice Gateway</option>
                                <option value="vapi">Vapi AI Voice Driver</option>
                                <option value="bland">Bland AI Gateway</option>
                            </select>
                        </div>
                        <div>
                            <label className="block font-semibold text-slate-300 mb-1">Recording Consent Requirement</label>
                            <select className="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-200 focus:outline-none">
                                <option value="required">Announce Consent at Start of Call (Compliant)</option>
                                <option value="implicit">Implicit Legal Consent Notice</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div className="space-y-4 pt-4 border-t border-slate-800">
                    <h3 className="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                        <Shield className="w-4 h-4 text-emerald-400" /> Recording Retention & Privacy Compliance
                    </h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label className="block font-semibold text-slate-300 mb-1">Audio Retention Days</label>
                            <input
                                type="number"
                                defaultValue={90}
                                className="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-slate-200"
                            />
                        </div>
                        <div>
                            <label className="block font-semibold text-slate-300 mb-1">Double Booking Protection</label>
                            <div className="p-2.5 rounded-xl bg-slate-950 border border-slate-800 text-emerald-400 font-bold">
                                Database Level Lock (Strict Transaction Lock Active)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};
