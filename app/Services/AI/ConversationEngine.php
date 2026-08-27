<?php

namespace App\Services\AI;

use App\Models\AIAgent;
use App\Models\AIAgentFaq;
use App\Models\AIAgentKnowledge;
use App\Models\Call;
use App\Models\CallOutcome;
use App\Models\CallRecording;
use App\Models\CallSummary;
use App\Models\CallTranscript;
use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\FollowUp;
use App\Services\Appointment\AppointmentAvailabilityService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ConversationEngine
{
    protected AppointmentAvailabilityService $slotService;

    public function __construct(AppointmentAvailabilityService $slotService)
    {
        $this->slotService = $slotService;
    }

    /**
     * Classify customer intent from raw speech text.
     */
    public function classifyIntent(string $text): string
    {
        $textLower = strtolower($text);

        if (Str::contains($textLower, ['6 pm', '5 pm', '7 pm', '10 am', '6pm', '5pm', '7pm', 'confirm', 'please confirm', 'confirm it'])) {
            return 'CONFIRM_SLOT';
        }
        if (Str::contains($textLower, ['book', 'appointment', 'schedule', 'slot', 'reserve', 'saturday', 'tomorrow'])) {
            return 'BOOK_APPOINTMENT';
        }
        if (Str::contains($textLower, ['reschedule', 'change time', 'change date', 'postpone'])) {
            return 'RESCHEDULE_APPOINTMENT';
        }
        if (Str::contains($textLower, ['cancel', 'don\'t want', 'remove appointment'])) {
            return 'CANCEL_APPOINTMENT';
        }
        if (Str::contains($textLower, ['human', 'person', 'representative', 'agent', 'manager', 'speak to someone'])) {
            return 'TALK_TO_HUMAN';
        }
        if (Str::contains($textLower, ['price', 'fee', 'cost', 'charge', 'rate', 'how much'])) {
            return 'PRICE_INFORMATION';
        }
        if (Str::contains($textLower, ['service', 'details', 'treatment', 'program', 'what do you offer'])) {
            return 'SERVICE_INFORMATION';
        }
        if (Str::contains($textLower, ['call later', 'busy right now', 'call me back', 'tomorrow evening'])) {
            return 'CALL_LATER';
        }
        if (Str::contains($textLower, ['not interested', 'stop calling', 'no thanks', 'don\'t call'])) {
            return 'NOT_INTERESTED';
        }
        if (Str::contains($textLower, ['yes', 'sure', 'interested', 'yeah', 'okay'])) {
            return 'INTERESTED';
        }

        return 'UNKNOWN';
    }

    /**
     * Process an interactive turn in simulated or live AI conversation.
     */
    public function processTurn(Call $call, string $customerInput): array
    {
        $agent = $call->aiAgent ?? AIAgent::first();
        $intent = $this->classifyIntent($customerInput);
        $aiResponse = "";
        $actionTaken = null;
        $appointmentCreated = null;

        switch ($intent) {
            case 'CONFIRM_SLOT':
                $textLower = strtolower($customerInput);
                $date = Carbon::now()->addDays(2)->format('Y-m-d');
                $startTime = Str::contains($textLower, '5') ? '17:00:00' : (Str::contains($textLower, '7') ? '19:00:00' : '18:00:00');
                $timeDisplay = Str::contains($textLower, '5') ? '5:00 PM' : (Str::contains($textLower, '7') ? '7:00 PM' : '6:00 PM');

                $appointmentCreated = $this->slotService->bookAppointment(
                    $call->customer_id,
                    $date,
                    $startTime,
                    $call->customer->interested_service_id,
                    'AI Call',
                    $call->id,
                    "Confirmed during live AI call"
                );

                $aiResponse = "Your appointment has been confirmed for " . Carbon::parse($date)->format('l') . " at {$timeDisplay}. Thank you and see you soon!";
                $actionTaken = 'CONFIRMED_APPOINTMENT';

                // Finalize Call record
                $call->update([
                    'status' => 'completed',
                    'end_time' => Carbon::now(),
                    'duration_seconds' => max(15, Carbon::parse($call->start_time)->diffInSeconds(Carbon::now())),
                ]);

                CallSummary::updateOrCreate(
                    ['call_id' => $call->id],
                    [
                        'purpose' => $call->call_purpose ?? 'Weight Loss Consultation Follow-Up',
                        'customer_interest' => 'High',
                        'main_discussion' => "Customer confirmed interest and booked {$timeDisplay} slot.",
                        'questions_asked' => 'Asked about available weekend slots.',
                        'objections_raised' => 'None',
                        'requested_service' => $call->customer->service?->name ?? 'Consultation',
                        'preferred_date' => $date,
                        'preferred_time' => $timeDisplay,
                        'appointment_status' => 'Confirmed',
                        'follow_up_required' => false,
                        'recommended_next_action' => 'Send SMS reminder 2 hours prior to consultation.',
                    ]
                );

                CallOutcome::updateOrCreate(
                    ['call_id' => $call->id],
                    [
                        'outcome' => 'Appointment Booked',
                        'confidence_score' => 0.99,
                        'notes' => 'Successfully booked appointment via live interactive AI call.',
                        'updated_by_user_id' => $call->user_id ?? 1,
                    ]
                );

                CallRecording::updateOrCreate(
                    ['call_id' => $call->id],
                    [
                        'customer_id' => $call->customer_id,
                        'recording_url' => 'https://cdn.freesound.org/previews/536/536108_11861866-lq.mp3',
                        'duration' => 35,
                        'file_size' => 1048576,
                        'consent_given' => true,
                    ]
                );
                break;

            case 'BOOK_APPOINTMENT':
            case 'INTERESTED':
                // Check available slots
                $date = Carbon::now()->addDays(2)->format('Y-m-d'); // Next Saturday/convenient date
                $slots = $this->slotService->getAvailableSlots($date, $call->customer->interested_service_id);
                $slotStrings = array_map(fn($s) => $s['start_time'], array_slice($slots, 0, 3));

                if (count($slotStrings) > 0) {
                    $slotsFormatted = implode(', ', $slotStrings);
                    $aiResponse = "We have " . Carbon::parse($date)->format('l (M d)') . " at {$slotsFormatted} available. Which time would you prefer?";
                    $actionTaken = 'OFFERED_SLOTS';
                } else {
                    $aiResponse = "I can arrange a custom slot for you. Would morning or evening work better?";
                    $actionTaken = 'NO_SLOTS_ASK_PREFERENCE';
                }
                break;

            case 'PRICE_INFORMATION':
                $service = $call->customer->service;
                $price = $service ? "\$" . number_format($service->price, 2) : "\$99.00";
                $aiResponse = "Our " . ($service ? $service->name : "Consultation") . " is currently priced at {$price}, which includes a comprehensive 1-on-1 body assessment. We have Saturday at 5 PM, 6 PM, or 7 PM available. Which time would you prefer?";
                $actionTaken = 'ANSWERED_PRICE';
                break;

            case 'SERVICE_INFORMATION':
                $aiResponse = "We provide personalized wellness and body transformation consultation tailored to your health goals. We have Saturday at 5 PM, 6 PM, or 7 PM available. Which time works best for you?";
                $actionTaken = 'ANSWERED_SERVICE_INFO';
                break;

            case 'TALK_TO_HUMAN':
                $aiResponse = "I understand. I'm transferring you to our senior wellness specialist right away. Please stay on the line.";
                $actionTaken = 'HUMAN_HANDOVER';
                // Log handover
                CustomerActivity::create([
                    'customer_id' => $call->customer_id,
                    'user_id' => $call->user_id,
                    'activity_type' => 'HUMAN_HANDOVER',
                    'description' => 'Customer requested human handover during AI Call.',
                ]);
                break;

            case 'CALL_LATER':
                $aiResponse = "No problem at all! I will schedule a follow-up call for tomorrow at a convenient time for you. Have a great day!";
                $actionTaken = 'SCHEDULED_FOLLOWUP';
                FollowUp::create([
                    'customer_id' => $call->customer_id,
                    'user_id' => $call->user_id,
                    'scheduled_at' => Carbon::now()->addDay()->setHour(14)->setMinute(0),
                    'reason' => 'Customer requested callback later.',
                    'status' => 'pending',
                ]);
                break;

            case 'NOT_INTERESTED':
                $aiResponse = "Thank you for your time. I will update your preferences so we won't disturb you again. Have a pleasant day!";
                $actionTaken = 'MARK_NOT_INTERESTED';
                Customer::where('id', $call->customer_id)->update(['lead_status' => 'Not Interested']);
                break;

            default:
                // FAQ search
                $faq = AIAgentFaq::where('ai_agent_id', $agent?->id)
                    ->get()
                    ->first(fn($f) => Str::contains(strtolower($customerInput), array_map('strtolower', $f->keywords ?? [])));

                if ($faq) {
                    $aiResponse = $faq->answer;
                    $actionTaken = 'ANSWERED_FAQ';
                } else {
                    $aiResponse = "That's a great question. Let me book a quick consultation so our specialist can provide tailored details for you. Shall we check Saturday times?";
                    $actionTaken = 'DEFAULT_PROMPT_BOOKING';
                }
                break;
        }

        return [
            'intent' => $intent,
            'ai_response' => $aiResponse,
            'action_taken' => $actionTaken,
        ];
    }

    /**
     * Execute complete mock AI call execution pipeline for a customer.
     */
    public function executeFullCallPipeline(Customer $customer, ?int $userId = null, ?int $agentId = null, string $purpose = "Weight Loss Consultation Follow-Up"): Call
    {
        $agent = $agentId ? AIAgent::find($agentId) : AIAgent::first();

        // 1. Create Call record
        $call = Call::create([
            'customer_id' => $customer->id,
            'user_id' => $userId ?? $customer->assigned_user_id ?? 1,
            'ai_agent_id' => $agent?->id ?? 1,
            'phone_number' => $customer->phone,
            'call_purpose' => $purpose,
            'status' => 'connected',
            'start_time' => Carbon::now(),
            'external_call_id' => 'call_exp_' . Str::random(10),
        ]);

        // 2. Generate Transcript & Dialogue
        $date = Carbon::now()->addDays(2)->format('Y-m-d');
        $availableSlots = $this->slotService->getAvailableSlots($date, $customer->interested_service_id);
        $chosenSlot = count($availableSlots) > 0 ? $availableSlots[0] : ['raw_start' => '10:00:00', 'start_time' => '10:00 AM'];
        $timeStr = $chosenSlot['start_time'];
        $startTime = $chosenSlot['raw_start'];

        $isTanglish = Str::contains(strtolower($agent?->language ?? 'tanglish'), ['tanglish', 'tamil', 'ta']);

        if ($isTanglish) {
            $dialogue = [
                ['speaker' => 'AI', 'text' => "Hello {$customer->name}, I'm calling from ABC Wellness regarding your recent enquiry. Is this a good time to speak?", 'timestamp' => '00:02'],
                ['speaker' => 'Customer', 'text' => "Yes, I am interested in booking a consultation.", 'timestamp' => '00:06'],
                ['speaker' => 'AI', 'text' => "Great! What day would be convenient for you?", 'timestamp' => '00:10'],
                ['speaker' => 'Customer', 'text' => "Saturday evening works for me.", 'timestamp' => '00:14'],
                ['speaker' => 'AI', 'text' => "We have Saturday at 5:00 PM, 6:00 PM, or 7:00 PM available. Which time would you prefer?", 'timestamp' => '00:19'],
                ['speaker' => 'Customer', 'text' => "6 PM.", 'timestamp' => '00:22'],
                ['speaker' => 'AI', 'text' => "Perfect. Shall I confirm your consultation for Saturday at 6:00 PM?", 'timestamp' => '00:26'],
                ['speaker' => 'Customer', 'text' => "Yes, please confirm it.", 'timestamp' => '00:29'],
                ['speaker' => 'AI', 'text' => "Your appointment has been confirmed for Saturday at 6:00 PM. Thank you and see you soon!", 'timestamp' => '00:35'],
            ];
        } else {
            $dialogue = [
                ['speaker' => 'AI', 'text' => "Hello {$customer->name}, I'm calling from ABC Wellness regarding your recent enquiry. Is this a good time to speak?", 'timestamp' => '00:02'],
                ['speaker' => 'Customer', 'text' => "Yes, I am interested in booking a consultation.", 'timestamp' => '00:06'],
                ['speaker' => 'AI', 'text' => "Great! What day would be convenient for you?", 'timestamp' => '00:10'],
                ['speaker' => 'Customer', 'text' => "Saturday evening works for me.", 'timestamp' => '00:14'],
                ['speaker' => 'AI', 'text' => "We have Saturday at 5:00 PM, 6:00 PM, or 7:00 PM available. Which time would you prefer?", 'timestamp' => '00:19'],
                ['speaker' => 'Customer', 'text' => "6 PM.", 'timestamp' => '00:22'],
                ['speaker' => 'AI', 'text' => "Perfect. Shall I confirm your consultation for Saturday at 6:00 PM?", 'timestamp' => '00:26'],
                ['speaker' => 'Customer', 'text' => "Yes, please confirm it.", 'timestamp' => '00:29'],
                ['speaker' => 'AI', 'text' => "Your appointment has been confirmed for Saturday at 6:00 PM. Thank you and see you soon!", 'timestamp' => '00:35'],
            ];
        }

        $fullTranscriptText = implode("\n", array_map(fn($d) => "{$d['speaker']}: {$d['text']}", $dialogue));

        CallTranscript::create([
            'call_id' => $call->id,
            'transcript_text' => $fullTranscriptText,
            'dialogue_json' => $dialogue,
        ]);

        // 3. Book Appointment using Double-Booking Safety Engine
        $appointment = $this->slotService->bookAppointment(
            $customer->id,
            $date,
            $startTime,
            $customer->interested_service_id,
            'AI Call',
            $call->id,
            "Confirmed during AI call with {$agent->name}"
        );

        // 4. Create Call Recording
        CallRecording::create([
            'call_id' => $call->id,
            'customer_id' => $customer->id,
            'recording_url' => 'https://cdn.freesound.org/previews/536/536108_11861866-lq.mp3',
            'duration' => 38,
            'file_size' => 1048576,
            'consent_given' => true,
            'retention_days' => 90,
        ]);

        // 5. Generate AI Call Summary
        CallSummary::create([
            'call_id' => $call->id,
            'purpose' => $purpose,
            'customer_interest' => 'High',
            'main_discussion' => "Customer confirmed interest in {$purpose} and selected Saturday 6 PM slot.",
            'questions_asked' => 'Asked about available weekend slots.',
            'objections_raised' => 'None',
            'requested_service' => $customer->service?->name ?? 'Consultation',
            'preferred_date' => $date,
            'preferred_time' => $timeStr,
            'appointment_status' => 'Confirmed',
            'follow_up_required' => false,
            'recommended_next_action' => 'Send SMS reminder 2 hours prior to consultation.',
        ]);

        // 6. Generate Call Outcome
        CallOutcome::create([
            'call_id' => $call->id,
            'outcome' => 'Appointment Booked',
            'confidence_score' => 0.99,
            'notes' => 'Successfully booked appointment via AI voice dialog.',
            'updated_by_user_id' => $userId ?? 1,
        ]);

        // 7. Update Call status & duration
        $call->update([
            'status' => 'completed',
            'end_time' => Carbon::now()->addSeconds(38),
            'duration_seconds' => 38,
        ]);

        // 8. Log activity
        CustomerActivity::create([
            'customer_id' => $customer->id,
            'user_id' => $userId ?? 1,
            'activity_type' => 'AI_CALL_COMPLETED',
            'description' => "AI Call completed. Appointment confirmed for {$date} at {$timeStr}.",
            'metadata' => [
                'call_id' => $call->id,
                'appointment_id' => $appointment->id,
            ],
        ]);

        return $call->fresh(['customer', 'transcript', 'recording', 'summary', 'outcome', 'appointment']);
    }
}
