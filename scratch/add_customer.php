<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\Service;

$service = Service::first();

$customer = Customer::updateOrCreate(
    ['phone' => '+918825816433'],
    [
        'name' => 'Karthik (8825816433)',
        'email' => 'karthik@example.com',
        'gender' => 'Male',
        'location' => 'Tamil Nadu, India',
        'source' => 'Direct Phone Request',
        'lead_status' => 'New',
        'interested_service_id' => $service?->id,
        'notes' => 'Customer requested real AI Voice call on +918825816433.',
        'assigned_user_id' => 1,
    ]
);

echo "Customer added/updated successfully!\n";
echo "ID: {$customer->id}\n";
echo "Name: {$customer->name}\n";
echo "Phone: {$customer->phone}\n";
