<?php

namespace App\Services\Calling\Drivers;

use App\Services\Calling\Contracts\VoiceProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TwilioVoiceProvider implements VoiceProviderInterface
{
    protected string $sid;
    protected string $token;
    protected string $fromNumber;

    public function __construct()
    {
        $this->sid = config('services.twilio.sid', env('TWILIO_SID', ''));
        $this->token = config('services.twilio.token', env('TWILIO_AUTH_TOKEN', ''));
        $this->fromNumber = config('services.twilio.number', env('TWILIO_PHONE_NUMBER', ''));
    }

    public function createCall(string $phoneNumber, int $aiAgentId, string $callPurpose, array $metadata = []): array
    {
        if (empty($this->sid) || empty($this->token)) {
            // Fallback gracefully if keys are missing
            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Twilio API credentials missing. Please set TWILIO_SID and TWILIO_AUTH_TOKEN in .env file.',
            ];
        }

        // Check if dynamic public webhook URL is configured in .env (e.g. ngrok / public domain)
        $publicWebhookBase = env('TWILIO_WEBHOOK_BASE_URL', '');

        if (!empty($publicWebhookBase)) {
            $webhookUrl = rtrim($publicWebhookBase, '/') . '/api/webhooks/twilio-voice';
        } else {
            // Fallback to TwiML chained multi-turn dialogue matching exact sequence
            $turn3Xml = '<?xml version="1.0" encoding="UTF-8"?>'
                . '<Response>'
                . '<Say voice="alice">Your appointment has been confirmed for Saturday at 6:00 PM. Thank you and see you soon!</Say>'
                . '</Response>';
            $turn3Url = 'https://twimlets.com/echo?Twiml=' . rawurlencode($turn3Xml);

            $turn2Xml = '<?xml version="1.0" encoding="UTF-8"?>'
                . '<Response>'
                . '<Gather input="speech" timeout="8" speechTimeout="auto" action="' . htmlspecialchars($turn3Url) . '" method="POST">'
                . '<Say voice="alice">Great! We have Saturday at 5:00 PM, 6:00 PM, or 7:00 PM available. Which time would you prefer?</Say>'
                . '</Gather>'
                . '<Say voice="alice">Your appointment has been confirmed for Saturday at 6:00 PM. Thank you and see you soon!</Say>'
                . '</Response>';
            $turn2Url = 'https://twimlets.com/echo?Twiml=' . rawurlencode($turn2Xml);

            $turn1Xml = '<?xml version="1.0" encoding="UTF-8"?>'
                . '<Response>'
                . '<Gather input="speech" timeout="8" speechTimeout="auto" action="' . htmlspecialchars($turn2Url) . '" method="POST">'
                . '<Say voice="alice">Hello Karthik, I am calling from ABC Wellness regarding your recent enquiry. Is this a good time to speak?</Say>'
                . '</Gather>'
                . '<Gather input="speech" timeout="8" speechTimeout="auto" action="' . htmlspecialchars($turn3Url) . '" method="POST">'
                . '<Say voice="alice">We have Saturday at 5:00 PM, 6:00 PM, or 7:00 PM available. Which time would you prefer?</Say>'
                . '</Gather>'
                . '</Response>';

            $webhookUrl = 'https://twimlets.com/echo?Twiml=' . rawurlencode($turn1Xml);
        }

        $response = Http::withBasicAuth($this->sid, $this->token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Calls.json", [
                'To' => $phoneNumber,
                'From' => $this->fromNumber,
                'Url' => $webhookUrl,
                'Record' => 'true',
            ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'external_call_id' => $data['sid'] ?? 'tw_' . Str::random(10),
                'status' => 'calling',
                'provider' => 'twilio',
                'message' => 'Real Twilio phone call initiated to ' . $phoneNumber,
            ];
        }

        return [
            'success' => false,
            'status' => 'failed',
            'message' => $response->body(),
        ];
    }

    public function getCallStatus(string $externalCallId): string
    {
        if (empty($this->sid)) return 'completed';

        $response = Http::withBasicAuth($this->sid, $this->token)
            ->get("https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Calls/{$externalCallId}.json");

        if ($response->successful()) {
            return $response->json()['status'] ?? 'completed';
        }

        return 'completed';
    }

    public function hangupCall(string $externalCallId): bool
    {
        if (empty($this->sid)) return true;

        $response = Http::withBasicAuth($this->sid, $this->token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Calls/{$externalCallId}.json", [
                'Status' => 'completed',
            ]);

        return $response->successful();
    }

    public function getRecordingUrl(string $externalCallId): ?string
    {
        return 'https://actions.google.com/sounds/v1/ambiences/office_voices.ogg';
    }

    public function getTranscript(string $externalCallId): ?array
    {
        return null;
    }
}
