<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Appointment;
use App\Models\Call;
use App\Models\CallOutcome;
use App\Models\CallRecording;
use App\Models\CallSummary;
use App\Models\CallTranscript;
use Illuminate\Support\Facades\DB;

DB::statement('PRAGMA foreign_keys = OFF;');

CallRecording::truncate();
CallSummary::truncate();
CallOutcome::truncate();
CallTranscript::truncate();
Appointment::truncate();
Call::truncate();

DB::statement('PRAGMA foreign_keys = ON;');

echo "All AI Call Logs, Transcripts, Summaries, Recordings, and Appointments have been completely cleared!\n";
