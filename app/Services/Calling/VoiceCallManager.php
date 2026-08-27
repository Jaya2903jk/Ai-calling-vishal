<?php

namespace App\Services\Calling;

use App\Services\Calling\Contracts\VoiceProviderInterface;
use App\Services\Calling\Drivers\MockVoiceProvider;
use App\Services\Calling\Drivers\TwilioVoiceProvider;

class VoiceCallManager
{
    public static function make(): VoiceProviderInterface
    {
        $driver = env('VOICE_PROVIDER', 'mock');

        switch (strtolower($driver)) {
            case 'twilio':
                return new TwilioVoiceProvider();
            case 'mock':
            default:
                return new MockVoiceProvider();
        }
    }
}
