<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\CallRecording;
use App\Models\CallTranscript;
use App\Services\AI\ConversationEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwilioWebhookController extends Controller
{
    /**
     * TwiML Entry Point when Call connects.
     */
    public function handleVoice(Request $request)
    {
        $callSid = $request->input('CallSid');
        $to = $request->input('To');
        $from = $request->input('From');

        Log::info("Twilio Voice Call Connected: CallSid={$callSid}, From={$from}");

        $speechText = "Hello Karthik, I am calling from ABC Wellness regarding your recent enquiry. Is this a good time to speak?";

        // TwiML response with Speech Gather listener (Say inside Gather for seamless listening)
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Response>';
        $xml .= '<Gather input="speech" action="' . url('/api/webhooks/twilio-gather') . '" timeout="5" method="POST">';
        $xml .= '<Say voice="alice">' . htmlspecialchars($speechText) . '</Say>';
        $xml .= '</Gather>';
        $xml .= '<Say voice="alice">I did not catch that. Please stay on the line.</Say>';
        $xml .= '<Redirect>' . url('/api/webhooks/twilio-voice') . '</Redirect>';
        $xml .= '</Response>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Handle Customer Speech Input (STT) dynamically.
     */
    public function handleGather(Request $request, ConversationEngine $engine)
    {
        $speechResult = $request->input('SpeechResult', '');
        $callSid = $request->input('CallSid');

        Log::info("Customer Speech Input: SpeechResult='{$speechResult}'");

        $call = Call::where('external_call_id', $callSid)->latest()->first() ?? Call::latest()->first();

        if ($call && !empty($speechResult)) {
            $turnResult = $engine->processTurn($call, $speechResult);
            $aiResponse = $turnResult['ai_response'];

            // Append dialogue to transcript
            if ($call->transcript) {
                $dialogue = $call->transcript->dialogue_json ?? [];
                $dialogue[] = ['speaker' => 'Customer', 'text' => $speechResult, 'timestamp' => date('H:i:s')];
                $dialogue[] = ['speaker' => 'AI', 'text' => $aiResponse, 'timestamp' => date('H:i:s')];

                $call->transcript->update([
                    'dialogue_json' => $dialogue,
                    'transcript_text' => implode("\n", array_map(fn($d) => "{$d['speaker']}: {$d['text']}", $dialogue)),
                ]);
            }

            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<Response>';
            if ($turnResult['intent'] !== 'NOT_INTERESTED' && $turnResult['action_taken'] !== 'CONFIRMED_APPOINTMENT') {
                $xml .= '<Gather input="speech" action="' . url('/api/webhooks/twilio-gather') . '" timeout="5" method="POST">';
                $xml .= '<Say voice="alice">' . htmlspecialchars($aiResponse) . '</Say>';
                $xml .= '</Gather>';
            } else {
                $xml .= '<Say voice="alice">' . htmlspecialchars($aiResponse) . '</Say>';
            }
            $xml .= '</Response>';

            return response($xml, 200)->header('Content-Type', 'text/xml');
        }

        // If customer was silent or speech was empty, prompt again without disconnecting
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Response>';
        $xml .= '<Gather input="speech" action="' . url('/api/webhooks/twilio-gather') . '" timeout="5" method="POST">';
        $xml .= '<Say voice="alice">I am still on the line. Could you please repeat your request?</Say>';
        $xml .= '</Gather>';
        $xml .= '<Say voice="alice">Thank you for calling ABC Wellness. Goodbye!</Say>';
        $xml .= '</Response>';
        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Save Real Audio Recording URL from Twilio.
     */
    public function handleRecording(Request $request)
    {
        $callSid = $request->input('CallSid');
        $recordingUrl = $request->input('RecordingUrl');
        $duration = $request->input('RecordingDuration', 30);

        Log::info("Twilio Audio Recording Received: RecordingUrl={$recordingUrl}");

        if ($recordingUrl) {
            $call = Call::where('external_call_id', $callSid)->latest()->first() ?? Call::latest()->first();
            if ($call) {
                CallRecording::updateOrCreate(
                    ['call_id' => $call->id],
                    [
                        'customer_id' => $call->customer_id,
                        'recording_url' => $recordingUrl . '.mp3',
                        'duration' => $duration,
                        'file_size' => 1048576,
                        'consent_given' => true,
                    ]
                );
            }
        }

        return response()->json(['success' => true]);
    }
}
