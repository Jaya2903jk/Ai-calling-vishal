<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Services\AI\ConversationEngine;

$engine = app(ConversationEngine::class);
$customer = Customer::find(2);

echo "Testing AI Conversation Pipeline for Customer: {$customer->name}\n";
$call = $engine->executeFullCallPipeline($customer, 1, 1, "Body Composition AI Assessment");

echo "SUCCESS!\n";
echo "Call ID: {$call->id}\n";
echo "Status: {$call->status}\n";
echo "Duration: {$call->duration_seconds}s\n";
echo "Outcome: {$call->outcome->outcome}\n";
echo "Appointment Date & Time: {$call->appointment->date} @ {$call->appointment->start_time}\n";
