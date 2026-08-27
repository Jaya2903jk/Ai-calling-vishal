<?php

namespace Database\Seeders;

use App\Models\AIAgent;
use App\Models\AIAgentFaq;
use App\Models\AIAgentKnowledge;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Call;
use App\Models\CallOutcome;
use App\Models\CallRecording;
use App\Models\CallSummary;
use App\Models\CallTranscript;
use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\CustomerNote;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $superAdminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'permissions' => ['*'],
        ]);
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'permissions' => ['manage_users', 'manage_calls', 'manage_agents', 'view_reports'],
        ]);
        $managerRole = Role::create([
            'name' => 'Manager',
            'slug' => 'manager',
            'permissions' => ['manage_calls', 'view_reports', 'manage_customers'],
        ]);
        $employeeRole = Role::create([
            'name' => 'Employee',
            'slug' => 'employee',
            'permissions' => ['view_calls', 'manage_customers'],
        ]);

        // 2. Users
        $admin = User::create([
            'name' => 'Dr. Vikram Seth',
            'email' => 'admin@abcwellness.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'phone' => '+919876543210',
            'department' => 'Management',
            'status' => 'active',
        ]);

        $employee = User::create([
            'name' => 'Priya Sharma',
            'email' => 'priya@abcwellness.com',
            'password' => Hash::make('password'),
            'role_id' => $employeeRole->id,
            'phone' => '+919876543211',
            'department' => 'Client Care',
            'status' => 'active',
        ]);

        // 3. Services
        $weightLossService = Service::create([
            'name' => 'Weight Loss Consultation',
            'description' => 'Comprehensive 1-on-1 nutritional evaluation and custom weight management plan.',
            'duration_minutes' => 45,
            'price' => 99.00,
            'is_active' => true,
        ]);

        $bodyCompService = Service::create([
            'name' => 'Body Composition Scan',
            'description' => 'Advanced DEXA body fat, muscle mass, and visceral fat analysis.',
            'duration_minutes' => 30,
            'price' => 49.00,
            'is_active' => true,
        ]);

        $dietService = Service::create([
            'name' => 'Personalized Diet Planning',
            'description' => 'Customized macro-balanced meal calendar created by certified clinical dietitians.',
            'duration_minutes' => 30,
            'price' => 79.00,
            'is_active' => true,
        ]);

        // 4. AI Voice Agent
        $agent = AIAgent::create([
            'name' => 'ABC Wellness AI Assistant',
            'voice' => 'en-IN-Wavenet-D',
            'language' => 'English / Tanglish',
            'business_name' => 'ABC Wellness Center',
            'greeting' => "Hello, I'm calling from ABC Wellness regarding your recent enquiry. Is this a good time to speak?",
            'personality' => 'Friendly, professional, empathetic, and attentive',
            'system_instructions' => 'Introduce ABC Wellness, answer service questions, check real-time available slots, confirm Saturday/Sunday appointments, and handle objections warmly.',
            'allowed_services' => ['Weight Loss Consultation', 'Body Composition Scan', 'Personalized Diet Planning'],
            'faq_knowledge' => [
                'fees' => 'Consultation starts at \$99.',
                'location' => 'Main Center at Jubilee Hills / Indiranagar.',
            ],
            'appointment_rules' => [
                'max_per_day' => 10,
                'lead_time_hours' => 2,
            ],
            'escalation_rules' => [
                'transfer_to_human' => true,
                'human_number' => '+919876543210',
            ],
            'call_ending_message' => 'Your appointment has been confirmed. Thank you for choosing ABC Wellness!',
            'is_active' => true,
        ]);

        // 5. Knowledge & FAQs
        AIAgentKnowledge::create([
            'ai_agent_id' => $agent->id,
            'category' => 'Company',
            'title' => 'Working Hours & Location',
            'content' => 'Open Monday to Saturday, 9:00 AM to 8:00 PM. Located at Suite 402, Wellness Plaza.',
        ]);

        AIAgentKnowledge::create([
            'ai_agent_id' => $agent->id,
            'category' => 'Pricing & Offers',
            'title' => 'First Visit Special Offer',
            'content' => 'First consultation includes a complimentary body mass analysis report.',
        ]);

        AIAgentFaq::create([
            'ai_agent_id' => $agent->id,
            'question' => 'What is the consultation fee?',
            'answer' => 'Our initial consultation fee is \$99, which includes a full health evaluation and body composition report.',
            'keywords' => ['fee', 'cost', 'price', 'charge', 'rate'],
        ]);

        AIAgentFaq::create([
            'ai_agent_id' => $agent->id,
            'question' => 'Can I reschedule my appointment?',
            'answer' => 'Yes, you can reschedule at any time up to 2 hours prior to your slot with no penalty fee.',
            'keywords' => ['reschedule', 'change', 'postpone'],
        ]);

        // 6. Customers
        $raj = Customer::create([
            'name' => 'Raj Kumar',
            'phone' => '+919876500001',
            'email' => 'raj.kumar@example.com',
            'gender' => 'Male',
            'dob' => '1990-05-15',
            'location' => 'Chennai',
            'source' => 'Facebook Ad',
            'lead_status' => 'Appointment Booked',
            'interested_service_id' => $weightLossService->id,
            'notes' => 'Enquired about weight loss plan for upcoming wedding.',
            'assigned_user_id' => $employee->id,
        ]);

        $anitha = Customer::create([
            'name' => 'Anitha Ramesh',
            'phone' => '+919876500002',
            'email' => 'anitha.r@example.com',
            'gender' => 'Female',
            'dob' => '1993-08-22',
            'location' => 'Bengaluru',
            'source' => 'Google Search',
            'lead_status' => 'Interested',
            'interested_service_id' => $bodyCompService->id,
            'notes' => 'Wants body fat percentage scan before starting gym regime.',
            'assigned_user_id' => $employee->id,
        ]);

        $vikram = Customer::create([
            'name' => 'Vikram Singh',
            'phone' => '+919876500003',
            'email' => 'vikram.s@example.com',
            'gender' => 'Male',
            'dob' => '1985-11-10',
            'location' => 'Hyderabad',
            'source' => 'Website Enquiry',
            'lead_status' => 'Contacted',
            'interested_service_id' => $dietService->id,
            'notes' => 'Needs diabetic-friendly keto meal planning.',
            'assigned_user_id' => $admin->id,
        ]);

        // 7. Calls & Transcripts for Raj
        $callRaj = Call::create([
            'customer_id' => $raj->id,
            'user_id' => $employee->id,
            'ai_agent_id' => $agent->id,
            'phone_number' => $raj->phone,
            'call_purpose' => 'Weight Loss Consultation Follow-Up',
            'status' => 'completed',
            'start_time' => Carbon::now()->subHours(2),
            'end_time' => Carbon::now()->subHours(2)->addSeconds(39),
            'duration_seconds' => 39,
            'external_call_id' => 'call_raj_001',
        ]);

        $dialogueRaj = [
            ['speaker' => 'AI', 'text' => "Hello Raj, I'm calling from ABC Wellness regarding your recent enquiry. Is this a good time to speak?", 'timestamp' => '00:02'],
            ['speaker' => 'Customer', 'text' => "Yes.", 'timestamp' => '00:05'],
            ['speaker' => 'AI', 'text' => "Great! I wanted to check whether you're interested in booking a consultation.", 'timestamp' => '00:09'],
            ['speaker' => 'Customer', 'text' => "Yes, I am interested.", 'timestamp' => '00:13'],
            ['speaker' => 'AI', 'text' => "Sure. What day would be convenient for you?", 'timestamp' => '00:16'],
            ['speaker' => 'Customer', 'text' => "Saturday evening.", 'timestamp' => '00:20'],
            ['speaker' => 'AI', 'text' => "We have Saturday at 5:00 PM, 6:00 PM, or 7:00 PM available. Which time would you prefer?", 'timestamp' => '00:25'],
            ['speaker' => 'Customer', 'text' => "6 PM.", 'timestamp' => '00:28'],
            ['speaker' => 'AI', 'text' => "Perfect. Shall I confirm your consultation for Saturday at 6:00 PM?", 'timestamp' => '00:32'],
            ['speaker' => 'Customer', 'text' => "Yes.", 'timestamp' => '00:34'],
            ['speaker' => 'AI', 'text' => "Your appointment has been confirmed for Saturday at 6:00 PM. Thank you.", 'timestamp' => '00:39'],
        ];

        CallTranscript::create([
            'call_id' => $callRaj->id,
            'transcript_text' => implode("\n", array_map(fn($d) => "{$d['speaker']}: {$d['text']}", $dialogueRaj)),
            'dialogue_json' => $dialogueRaj,
        ]);

        CallRecording::create([
            'call_id' => $callRaj->id,
            'customer_id' => $raj->id,
            'recording_url' => 'https://actions.google.com/sounds/v1/ambiences/office_voices.ogg',
            'duration' => 39,
            'file_size' => 1048576,
            'consent_given' => true,
            'retention_days' => 90,
        ]);

        CallSummary::create([
            'call_id' => $callRaj->id,
            'purpose' => 'Weight Loss Consultation Follow-Up',
            'customer_interest' => 'High',
            'main_discussion' => 'Customer requested Saturday evening. 6 PM slot offered and confirmed.',
            'questions_asked' => 'Available times for Saturday evening.',
            'objections_raised' => 'None',
            'requested_service' => 'Weight Loss Consultation',
            'preferred_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'preferred_time' => '6:00 PM',
            'appointment_status' => 'Confirmed',
            'follow_up_required' => false,
            'recommended_next_action' => 'Prepare intake evaluation form prior to Saturday 6 PM slot.',
        ]);

        CallOutcome::create([
            'call_id' => $callRaj->id,
            'outcome' => 'Appointment Booked',
            'confidence_score' => 0.99,
            'notes' => 'Customer confirmed Saturday 6:00 PM slot.',
            'updated_by_user_id' => $employee->id,
        ]);

        // 8. Appointment for Raj
        Appointment::create([
            'customer_id' => $raj->id,
            'user_id' => $employee->id,
            'service_id' => $weightLossService->id,
            'date' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'start_time' => '18:00:00',
            'end_time' => '18:45:00',
            'status' => 'confirmed',
            'booking_source' => 'AI Call',
            'notes' => 'Booked automatically by ABC Wellness AI Assistant during call #call_raj_001.',
            'call_id' => $callRaj->id,
        ]);

        // 9. Customer Activity Timeline
        CustomerActivity::create([
            'customer_id' => $raj->id,
            'user_id' => $employee->id,
            'activity_type' => 'CUSTOMER_CREATED',
            'description' => 'Customer lead added from Facebook Ad.',
        ]);

        CustomerActivity::create([
            'customer_id' => $raj->id,
            'user_id' => $employee->id,
            'activity_type' => 'AI_CALL_INITIATED',
            'description' => 'AI Voice Call initiated by Priya Sharma.',
        ]);

        CustomerActivity::create([
            'customer_id' => $raj->id,
            'user_id' => $employee->id,
            'activity_type' => 'APPOINTMENT_BOOKED',
            'description' => 'Appointment confirmed for Saturday at 6:00 PM via AI Voice Assistant.',
        ]);

        CustomerNote::create([
            'customer_id' => $raj->id,
            'user_id' => $employee->id,
            'note' => 'Raj prefers evening slots due to work schedule.',
        ]);

        // 10. Notifications
        Notification::create([
            'user_id' => $admin->id,
            'type' => 'APPOINTMENT_BOOKED',
            'title' => 'New AI Appointment Booked',
            'message' => 'Raj Kumar booked Weight Loss Consultation for Saturday at 6:00 PM via AI Call.',
            'data' => ['customer_id' => $raj->id, 'call_id' => $callRaj->id],
            'is_read' => false,
        ]);
    }
}
