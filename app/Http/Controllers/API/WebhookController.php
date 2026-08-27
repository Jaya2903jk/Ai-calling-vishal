<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $eventId = $request->input('event_id', $request->header('X-Event-ID', uniqid('evt_')));
        $provider = $request->input('provider', 'generic_voice');
        $eventType = $request->input('event_type', 'call.status');

        // Idempotency check: process each event ID only once
        $existing = WebhookEvent::where('event_id', $eventId)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Event already processed (idempotent)',
            ], 200);
        }

        $event = WebhookEvent::create([
            'event_id' => $eventId,
            'provider' => $provider,
            'event_type' => $eventType,
            'payload' => $request->all(),
            'processed_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook received and logged successfully',
            'event_id' => $event->event_id,
        ]);
    }
}
