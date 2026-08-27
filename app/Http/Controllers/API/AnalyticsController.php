<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Call;
use App\Models\CallOutcome;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function callAnalytics()
    {
        $totalCalls = Call::count();
        $completedCalls = Call::where('status', 'completed')->count();
        $failedCalls = Call::whereIn('status', ['failed', 'no_answer', 'busy'])->count();
        $avgDuration = round(Call::where('status', 'completed')->avg('duration_seconds') ?? 0);
        $answerRate = $totalCalls > 0 ? round(($completedCalls / $totalCalls) * 100, 1) : 0;
        $conversionRate = $totalCalls > 0 ? round((Appointment::count() / $totalCalls) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'analytics' => [
                'total_calls' => $totalCalls,
                'completed_calls' => $completedCalls,
                'failed_calls' => $failedCalls,
                'avg_duration_seconds' => $avgDuration,
                'answer_rate_pct' => $answerRate,
                'conversion_rate_pct' => $conversionRate,
            ],
        ]);
    }

    public function salesAnalytics()
    {
        $interestedLeads = Customer::whereIn('lead_status', ['Interested', 'Appointment Booked', 'Converted'])->count();
        $appointmentsBooked = Appointment::where('status', 'confirmed')->count();
        $conversionPercentage = Customer::count() > 0 ? round(($appointmentsBooked / Customer::count()) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'analytics' => [
                'interested_leads' => $interestedLeads,
                'appointments_booked' => $appointmentsBooked,
                'conversion_percentage' => $conversionPercentage,
            ],
        ]);
    }

    public function aiAnalytics()
    {
        $totalConversations = Call::where('status', 'completed')->count();
        $handovers = CallOutcome::where('outcome', 'Human Handover')->count();
        $bookingRate = $totalConversations > 0 ? round((Appointment::where('booking_source', 'AI Call')->count() / $totalConversations) * 100, 1) : 0;
        $failureRate = Call::count() > 0 ? round((Call::where('status', 'failed')->count() / Call::count()) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'analytics' => [
                'successful_conversations' => $totalConversations,
                'ai_handovers' => $handovers,
                'ai_booking_rate_pct' => $bookingRate,
                'ai_failure_rate_pct' => $failureRate,
            ],
        ]);
    }
}
