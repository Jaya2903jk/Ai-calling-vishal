<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Calling\VoiceCallManager;

$provider = VoiceCallManager::make();
echo "Testing Twilio Voice Provider Call Dispatch to +918825816433...\n";

$result = $provider->createCall('+918825816433', 1, 'Weight Loss Follow Up');

print_r($result);
