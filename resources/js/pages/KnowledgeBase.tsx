import React, { useState, useEffect } from 'react';
import { BookOpen, HelpCircle, Plus, Trash2 } from 'lucide-react';
import api from '../api/client';

export const KnowledgeBase: React.FC = () => {
    const [knowledge, setKnowledge] = useState<any[]>([]);
    const [faqs, setFaqs] = useState<any[]>([]);

    useEffect(() => {
        api.get('/knowledge-base').then((res) => {
            if (res.data.success) {
                setKnowledge(res.data.knowledge);
                setFaqs(res.data.faqs);
            }
        }).catch(() => {});
    }, []);

    return (
        <div className="space-y-6">
            <div>
                <h1 className="text-xl font-bold text-white">AI Knowledge Base & FAQs</h1>
                <p className="text-xs text-slate-400">Configure business information, consultation pricing, working hours, and standard answers</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* FAQs */}
                <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                    <h3 className="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                        <HelpCircle className="w-4 h-4 text-cyan-400" /> Standard AI FAQs
                    </h3>
                    <div className="space-y-3">
                        {faqs.map((faq) => (
                            <div key={faq.id} className="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-1">
                                <h4 className="text-xs font-bold text-white">Q: {faq.question}</h4>
                                <p className="text-xs text-slate-300">A: {faq.answer}</p>
                            </div>
                        ))}
                    </div>
                </div>

                {/* General Info */}
                <div className="p-6 rounded-3xl bg-slate-900/90 border border-slate-800 space-y-4">
                    <h3 className="text-sm font-bold text-white flex items-center gap-2 border-b border-slate-800 pb-3">
                        <BookOpen className="w-4 h-4 text-cyan-400" /> Business Policies & Rules
                    </h3>
                    <div className="space-y-3">
                        {knowledge.map((k) => (
                            <div key={k.id} className="p-4 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-1">
                                <h4 className="text-xs font-bold text-cyan-400">{k.title}</h4>
                                <p className="text-xs text-slate-300">{k.content}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
};
