import React, { useState, useRef } from 'react';
import { Play, Pause, Volume2, Download, RotateCcw } from 'lucide-react';

interface AudioPlayerProps {
    src?: string;
    title?: string;
    duration?: number;
}

export const AudioPlayer: React.FC<AudioPlayerProps> = ({ src, title = "Call Audio Recording", duration = 38 }) => {
    const [isPlaying, setIsPlaying] = useState(false);
    const [currentTime, setCurrentTime] = useState(0);
    const audioRef = useRef<HTMLAudioElement | null>(null);

    const audioUrl = src && src.startsWith('http') ? src : 'https://cdn.freesound.org/previews/536/536108_11861866-lq.mp3';

    const playSpeechUtterance = () => {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const textToSpeak = "Hello Karthik, I am calling from ABC Wellness regarding your recent enquiry. Is this a good time to speak? ... Yes, I am interested in booking a consultation. ... Great! What day would be convenient for you? ... Saturday evening works for me. ... We have Saturday at 5:00 PM, 6:00 PM, or 7:00 PM available. Which time would you prefer? ... 6 PM. ... Perfect. Shall I confirm your consultation for Saturday at 6:00 PM? ... Yes, please confirm it. ... Your appointment has been confirmed for Saturday at 6:00 PM. Thank you and see you soon!";
            const utterance = new SpeechSynthesisUtterance(textToSpeak);
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            utterance.onstart = () => setIsPlaying(true);
            utterance.onend = () => setIsPlaying(false);
            utterance.onerror = () => setIsPlaying(false);
            window.speechSynthesis.speak(utterance);
        }
    };

    const togglePlay = () => {
        if (isPlaying) {
            if (audioRef.current && !audioRef.current.paused) {
                audioRef.current.pause();
            }
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
            setIsPlaying(false);
        } else {
            if (audioRef.current) {
                audioRef.current.play().then(() => {
                    setIsPlaying(true);
                }).catch(() => {
                    // Fallback to Web Speech API narration if HTML5 Audio fails / blocked
                    playSpeechUtterance();
                });
            } else {
                playSpeechUtterance();
            }
        }
    };

    const handleTimeUpdate = () => {
        if (audioRef.current) {
            setCurrentTime(audioRef.current.currentTime);
        }
    };

    const formatTime = (secs: number) => {
        const m = Math.floor(secs / 60);
        const s = Math.floor(secs % 60);
        return `${m}:${s < 10 ? '0' : ''}${s}`;
    };

    return (
        <div className="p-4 rounded-2xl bg-slate-900/90 border border-cyan-500/30 shadow-lg shadow-cyan-500/10">
            <audio
                ref={audioRef}
                src={audioUrl}
                onTimeUpdate={handleTimeUpdate}
                onEnded={() => setIsPlaying(false)}
            />
            <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2">
                    <div className="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></div>
                    <span className="text-xs font-semibold text-white tracking-wide">{title}</span>
                </div>
                <span className="text-[11px] font-mono text-cyan-400">
                    {formatTime(currentTime)} / {formatTime(duration)}
                </span>
            </div>

            {/* Simulated Waveform & Progress */}
            <div className="relative h-8 flex items-center gap-1 my-2 px-1">
                {[40, 65, 30, 85, 90, 45, 60, 75, 100, 50, 70, 85, 40, 60, 95, 80, 50, 70, 30, 60, 85, 90, 40].map((h, idx) => {
                    const progress = (idx / 23) * duration;
                    const isPassed = currentTime >= progress;
                    return (
                        <div
                            key={idx}
                            style={{ height: `${h}%` }}
                            className={`flex-1 rounded-full transition-all duration-300 ${
                                isPassed ? 'bg-gradient-to-t from-cyan-500 to-blue-500' : 'bg-slate-800'
                            }`}
                        />
                    );
                })}
            </div>

            {/* Controls */}
            <div className="flex items-center justify-between mt-3 pt-2 border-t border-slate-800/60">
                <div className="flex items-center gap-3">
                    <button
                        onClick={togglePlay}
                        className="w-9 h-9 rounded-full bg-cyan-500 hover:bg-cyan-400 text-slate-950 flex items-center justify-center font-bold transition-all shadow-md shadow-cyan-500/30 cursor-pointer"
                    >
                        {isPlaying ? <Pause className="w-4 h-4 fill-slate-950" /> : <Play className="w-4 h-4 fill-slate-950 ml-0.5" />}
                    </button>
                    <button
                        onClick={() => {
                            if (audioRef.current) {
                                audioRef.current.currentTime = 0;
                            }
                            if ('speechSynthesis' in window) {
                                window.speechSynthesis.cancel();
                            }
                            setIsPlaying(false);
                        }}
                        className="p-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition-all cursor-pointer"
                    >
                        <RotateCcw className="w-3.5 h-3.5" />
                    </button>
                </div>

                <div className="flex items-center gap-2">
                    <Volume2 className="w-4 h-4 text-slate-400" />
                    <button
                        onClick={playSpeechUtterance}
                        className="p-1.5 rounded-lg text-xs font-semibold text-cyan-400 bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/30 transition-all cursor-pointer"
                    >
                        Read AI Speech Out Loud
                    </button>
                </div>
            </div>
        </div>
    );
};
