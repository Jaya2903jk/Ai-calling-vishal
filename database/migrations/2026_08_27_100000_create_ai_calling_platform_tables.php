<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Super Admin, Admin, Manager, Employee
            $table->string('slug')->unique();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        // 2. Services
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->default(30);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Update default users table or add columns
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->string('department')->nullable()->after('phone');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('department');
        });

        // 3. Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('location')->nullable();
            $table->string('source')->default('Website Enquiry');
            $table->enum('lead_status', [
                'New', 'Contacted', 'Interested', 'Follow Up', 'Appointment Booked', 'Converted', 'Not Interested', 'Closed'
            ])->default('New');
            $table->foreignId('interested_service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Customer Notes
        Schema::create('customer_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note');
            $table->timestamps();
        });

        // 5. Customer Activities
        Schema::create('customer_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity_type');
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 6. AI Agents
        Schema::create('ai_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('voice')->default('en-US-Neural2-F');
            $table->string('language')->default('English');
            $table->string('business_name')->default('ABC Wellness');
            $table->text('greeting')->nullable();
            $table->string('personality')->default('Friendly, professional and helpful');
            $table->text('system_instructions')->nullable();
            $table->json('allowed_services')->nullable();
            $table->json('faq_knowledge')->nullable();
            $table->json('appointment_rules')->nullable();
            $table->json('escalation_rules')->nullable();
            $table->text('call_ending_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 7. AI Agent Knowledge
        Schema::create('ai_agent_knowledge', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_agent_id')->nullable()->constrained('ai_agents')->cascadeOnDelete();
            $table->string('category');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        // 8. AI Agent FAQs
        Schema::create('ai_agent_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_agent_id')->nullable()->constrained('ai_agents')->cascadeOnDelete();
            $table->text('question');
            $table->text('answer');
            $table->json('keywords')->nullable();
            $table->timestamps();
        });

        // 9. Campaigns
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('ai_agent_id')->nullable()->constrained('ai_agents')->nullOnDelete();
            $table->string('call_purpose')->default('Outreach');
            $table->dateTime('scheduled_start')->nullable();
            $table->time('calling_hours_start')->default('09:00:00');
            $table->time('calling_hours_end')->default('19:00:00');
            $table->enum('status', ['draft', 'running', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->integer('total_contacts')->default(0);
            $table->integer('completed_calls')->default(0);
            $table->integer('failed_calls')->default(0);
            $table->integer('conversions')->default(0);
            $table->timestamps();
        });

        // 10. Calls
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ai_agent_id')->nullable()->constrained('ai_agents')->nullOnDelete();
            $table->string('phone_number');
            $table->string('call_purpose')->default('Consultation Booking');
            $table->enum('status', [
                'pending', 'queued', 'calling', 'connected', 'completed', 'failed', 'no_answer', 'busy', 'cancelled'
            ])->default('pending');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->string('external_call_id')->nullable();
            $table->foreignId('initiated_by_campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->timestamps();
        });

        // 11. Call Transcripts
        Schema::create('call_transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('calls')->cascadeOnDelete();
            $table->longText('transcript_text');
            $table->json('dialogue_json');
            $table->timestamps();
        });

        // 12. Call Recordings
        Schema::create('call_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('calls')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('recording_url');
            $table->integer('duration')->default(0);
            $table->bigInteger('file_size')->default(0);
            $table->boolean('consent_given')->default(true);
            $table->integer('retention_days')->default(90);
            $table->timestamps();
        });

        // 13. Call Summaries
        Schema::create('call_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('calls')->cascadeOnDelete();
            $table->string('purpose')->nullable();
            $table->string('customer_interest')->nullable();
            $table->text('main_discussion')->nullable();
            $table->text('questions_asked')->nullable();
            $table->text('objections_raised')->nullable();
            $table->string('requested_service')->nullable();
            $table->string('preferred_date')->nullable();
            $table->string('preferred_time')->nullable();
            $table->string('appointment_status')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->text('recommended_next_action')->nullable();
            $table->timestamps();
        });

        // 14. Call Outcomes
        Schema::create('call_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('calls')->cascadeOnDelete();
            $table->enum('outcome', [
                'Interested', 'Not Interested', 'Appointment Booked', 'Call Later', 'No Answer',
                'Busy', 'Wrong Number', 'Human Handover', 'Service Question', 'Complaint'
            ]);
            $table->float('confidence_score')->default(0.95);
            $table->text('notes')->nullable();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 15. Appointments
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', [
                'pending', 'confirmed', 'completed', 'cancelled', 'rescheduled', 'no_show'
            ])->default('confirmed');
            $table->enum('booking_source', [
                'Employee', 'AI Call', 'Website', 'API', 'Admin'
            ])->default('AI Call');
            $table->text('notes')->nullable();
            $table->foreignId('call_id')->nullable()->constrained('calls')->nullOnDelete();
            $table->timestamps();
        });

        // 16. Appointment Slots
        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('service_id')->nullable()->constrained('services')->cascadeOnDelete();
            $table->boolean('is_booked')->default(false);
            $table->dateTime('locked_until')->nullable();
            $table->timestamps();

            $table->unique(['date', 'start_time', 'service_id']);
        });

        // 17. Follow Ups
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->boolean('auto_ai_call')->default(true);
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->foreignId('call_id')->nullable()->constrained('calls')->nullOnDelete();
            $table->timestamps();
        });

        // 18. Campaign Contacts
        Schema::create('campaign_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->enum('status', ['pending', 'queued', 'called', 'failed'])->default('pending');
            $table->foreignId('call_id')->nullable()->constrained('calls')->nullOnDelete();
            $table->timestamps();
        });

        // 19. Call Queue
        Schema::create('call_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('calls')->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->cascadeOnDelete();
            $table->integer('priority')->default(1);
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
            $table->integer('attempts')->default(0);
            $table->dateTime('available_at')->nullable();
            $table->timestamps();
        });

        // 20. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 21. Webhook Events
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('provider');
            $table->string('event_type');
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // 22. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('call_queue');
        Schema::dropIfExists('campaign_contacts');
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('appointment_slots');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('call_outcomes');
        Schema::dropIfExists('call_summaries');
        Schema::dropIfExists('call_recordings');
        Schema::dropIfExists('call_transcripts');
        Schema::dropIfExists('calls');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('ai_agent_faqs');
        Schema::dropIfExists('ai_agent_knowledge');
        Schema::dropIfExists('ai_agents');
        Schema::dropIfExists('customer_activities');
        Schema::dropIfExists('customer_notes');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('services');
        Schema::dropIfExists('roles');
    }
};
