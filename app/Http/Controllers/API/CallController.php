<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\CallOutcome;
use App\Models\Customer;
use App\Services\AI\ConversationEngine;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function index(Request $request)
    {
        $query = Call::with(['customer', 'aiAgent', 'summary', 'outcome', 'recording']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('customer', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $calls = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $calls,
        ]);
    }

    public function show($id)
    {
        $call = Call::with([
            'customer.service',
            'user',
            'aiAgent',
            'transcript',
            'recording',
            'summary',
            'outcome',
            'appointment.service',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'call' => $call,
        ]);
    }

    public function triggerCall(Request $request, ConversationEngine $engine)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'call_purpose' => 'nullable|string',
            'ai_agent_id' => 'nullable|exists:ai_agents,id',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $providerDriver = env('VOICE_PROVIDER', 'mock');

        if ($providerDriver === 'twilio') {
            $provider = \App\Services\Calling\VoiceCallManager::make();
            $result = $provider->createCall(
                $customer->phone,
                $request->ai_agent_id ?? 1,
                $request->call_purpose ?? 'Consultation Follow Up'
            );

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            // Create call in connected status while call is active on mobile
            $call = Call::create([
                'customer_id' => $customer->id,
                'user_id' => $request->user()?->id ?? 1,
                'ai_agent_id' => $request->ai_agent_id ?? 1,
                'phone_number' => $customer->phone,
                'call_purpose' => $request->call_purpose ?? 'Weight Loss Consultation Follow-Up',
                'status' => 'connected',
                'start_time' => \Carbon\Carbon::now(),
                'external_call_id' => $result['external_call_id'] ?? ('tw_' . \Illuminate\Support\Str::random(10)),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Real Twilio Call initiated to ' . $customer->phone,
                'call' => $call->fresh(['customer', 'aiAgent']),
                'twilio_result' => $result,
            ]);
        }

        $call = $engine->executeFullCallPipeline(
            $customer,
            $request->user()?->id,
            $request->ai_agent_id,
            $request->call_purpose ?? 'Consultation Follow Up'
        );

        return response()->json([
            'success' => true,
            'message' => 'AI Voice Call completed successfully.',
            'call' => $call,
        ]);
    }

    public function startInteractive(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'call_purpose' => 'nullable|string',
            'ai_agent_id' => 'nullable|exists:ai_agents,id',
        ]);

        $customer = Customer::findOrFail($request->customer_id);
        $agent = $request->ai_agent_id ? \App\Models\AIAgent::find($request->ai_agent_id) : \App\Models\AIAgent::first();

        $call = Call::create([
            'customer_id' => $customer->id,
            'user_id' => $request->user()?->id ?? 1,
            'ai_agent_id' => $agent?->id ?? 1,
            'phone_number' => $customer->phone,
            'call_purpose' => $request->call_purpose ?? 'Weight Loss Consultation Follow-Up',
            'status' => 'connected',
            'start_time' => \Carbon\Carbon::now(),
            'external_call_id' => 'live_call_' . \Illuminate\Support\Str::random(10),
        ]);

        $greetingText = "Hello {$customer->name}, I'm calling from ABC Wellness regarding your recent enquiry. Is this a good time to speak?";

        $initialDialogue = [
            ['speaker' => 'AI', 'text' => $greetingText, 'timestamp' => '00:02']
        ];

        \App\Models\CallTranscript::create([
            'call_id' => $call->id,
            'transcript_text' => "AI: {$greetingText}",
            'dialogue_json' => $initialDialogue,
        ]);

        return response()->json([
            'success' => true,
            'call' => $call->fresh(['customer', 'aiAgent', 'transcript']),
            'ai_greeting' => $greetingText,
        ]);
    }

    public function interactiveTurn(Request $request, ConversationEngine $engine)
    {
        $request->validate([
            'call_id' => 'required|exists:calls,id',
            'customer_input' => 'required|string',
        ]);

        $call = Call::with(['customer', 'aiAgent', 'transcript'])->findOrFail($request->call_id);
        $turnResult = $engine->processTurn($call, $request->customer_input);
        $aiResponse = $turnResult['ai_response'];

        // Get elapsed seconds
        $seconds = max(2, \Carbon\Carbon::parse($call->start_time)->diffInSeconds(\Carbon\Carbon::now()));
        $timestamp = sprintf('%02d:%02d', floor($seconds / 60), $seconds % 60);

        $dialogue = $call->transcript->dialogue_json ?? [];
        $dialogue[] = ['speaker' => 'Customer', 'text' => $request->customer_input, 'timestamp' => $timestamp];
        $dialogue[] = ['speaker' => 'AI', 'text' => $aiResponse, 'timestamp' => $timestamp];

        $call->transcript->update([
            'dialogue_json' => $dialogue,
            'transcript_text' => implode("\n", array_map(fn($d) => "{$d['speaker']}: {$d['text']}", $dialogue)),
        ]);

        $call = $call->fresh(['customer', 'transcript', 'appointment', 'summary', 'outcome', 'recording']);

        return response()->json([
            'success' => true,
            'call' => $call,
            'ai_response' => $aiResponse,
            'intent' => $turnResult['intent'],
            'action_taken' => $turnResult['action_taken'],
            'status' => $call->status,
        ]);
    }

    public function finalizeCall($id, ConversationEngine $engine)
    {
        $call = Call::findOrFail($id);
        $finalizedCall = $engine->executeFullCallPipeline($call->customer, $call->user_id, $call->ai_agent_id, $call->call_purpose);

        return response()->json([
            'success' => true,
            'call' => $finalizedCall,
        ]);
    }

    public function simulateTurn(Request $request, ConversationEngine $engine)
    {
        $request->validate([
            'call_id' => 'required|exists:calls,id',
            'customer_input' => 'required|string',
        ]);

        $call = Call::with(['customer', 'aiAgent'])->findOrFail($request->call_id);
        $result = $engine->processTurn($call, $request->customer_input);

        return response()->json([
            'success' => true,
            'intent' => $result['intent'],
            'ai_response' => $result['ai_response'],
            'action_taken' => $result['action_taken'],
        ]);
    }

    public function retryCall(Request $request, $id, ConversationEngine $engine)
    {
        $oldCall = Call::findOrFail($id);
        $call = $engine->executeFullCallPipeline(
            $oldCall->customer,
            $request->user()?->id,
            $oldCall->ai_agent_id,
            $oldCall->call_purpose
        );

        return response()->json([
            'success' => true,
            'message' => 'Call retried successfully',
            'call' => $call,
        ]);
    }

    public function updateOutcome(Request $request, $id)
    {
        $request->validate([
            'outcome' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $call = Call::findOrFail($id);

        CallOutcome::updateOrCreate(
            ['call_id' => $call->id],
            [
                'outcome' => $request->outcome,
                'notes' => $request->notes,
                'updated_by_user_id' => $request->user()?->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Call outcome updated successfully',
        ]);
    }
}
