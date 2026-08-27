<?php

namespace App\Services\Calling\Drivers;

use App\Services\Calling\Contracts\VoiceProviderInterface;
use Illuminate\Support\Str;

class MockVoiceProvider implements VoiceProviderInterface
{
    public function createCall(string $phoneNumber, int $aiAgentId, string $callPurpose, array $metadata = []): array
    {
        $externalId = 'mock_call_' . Str::random(12);

        return [
            'success' => true,
            'external_call_id' => $externalId,
            'status' => 'connected',
            'provider' => 'mock_ai_voice_v1',
            'message' => 'AI voice agent connected successfully (Mock Engine)',
        ];
    }

    public function getCallStatus(string $externalCallId): string
    {
        return 'completed';
    }

    public function hangupCall(string $externalCallId): bool
    {
        return true;
    }

    public function getRecordingUrl(string $externalCallId): ?string
    {
        // Public sample audio link for browser playback demonstration
        return 'https://actions.google.com/sounds/v1/ambiences/office_voices.ogg';
    }

    public function getTranscript(string $externalCallId): ?array
    {
        return [
            ['speaker' => 'AI', 'text' => "Hello Raj, I'm calling from ABC Wellness regarding your recent enquiry. Is this a good time to speak?", 'timestamp' => '00:02'],
            ['speaker' => 'Customer', 'text' => 'Yes, it is.', 'timestamp' => '00:05'],
            ['speaker' => 'AI', 'text' => 'Great! I wanted to check whether you are interested in booking a weight loss consultation.', 'timestamp' => '00:09'],
            ['speaker' => 'Customer', 'text' => 'Yes, I am interested.', 'timestamp' => '00:13'],
            ['speaker' => 'AI', 'text' => 'Sure. What day would be convenient for you?', 'timestamp' => '00:16'],
            ['speaker' => 'Customer', 'text' => 'Saturday evening.', 'timestamp' => '00:20'],
            ['speaker' => 'AI', 'text' => 'We have Saturday at 5:00 PM, 6:00 PM, or 7:00 PM available. Which time would you prefer?', 'timestamp' => '00:25'],
            ['speaker' => 'Customer', 'text' => '6 PM.', 'timestamp' => '00:28'],
            ['speaker' => 'AI', 'text' => 'Perfect. Shall I confirm your consultation for Saturday at 6:00 PM?', 'timestamp' => '00:32'],
            ['speaker' => 'Customer', 'text' => 'Yes, please.', 'timestamp' => '00:34'],
            ['speaker' => 'AI', 'text' => 'Your appointment has been confirmed for Saturday at 6:00 PM. Thank you and have a wonderful day!', 'timestamp' => '00:39'],
        ];
    }
}
