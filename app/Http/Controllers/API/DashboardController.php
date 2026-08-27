<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Call;
use App\Models\CallOutcome;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function metrics()
    {
        $totalCustomers = Customer::count();
        $totalCalls = Call::count();
        $successfulCalls = Call::where('status', 'completed')->count();
        $failedCalls = Call::whereIn('status', ['failed', 'no_answer', 'busy'])->count();
        $interestedLeads = Customer::whereIn('lead_status', ['Interested', 'Appointment Booked', 'Converted'])->count();
        $appointmentsBooked = Appointment::where('status', 'confirmed')->count();
        $appointmentsPending = Appointment::where('status', 'pending')->count();
        $todayCalls = Call::whereDate('created_at', Carbon::today())->count();
        $upcomingAppointments = Appointment::where('date', '>=', Carbon::today())
            ->where('status', 'confirmed')
            ->count();

        $avgDuration = Call::where('status', 'completed')->avg('duration_seconds') ?? 0;
        $conversionRate = $totalCalls > 0 ? round(($appointmentsBooked / $totalCalls) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'metrics' => [
                'total_customers' => $totalCustomers,
                'total_calls' => $totalCalls,
                'successful_calls' => $successfulCalls,
                'failed_calls' => $failedCalls,
                'interested_leads' => $interestedLeads,
                'appointments_booked' => $appointmentsBooked,
                'appointments_pending' => $appointmentsPending,
                'today_calls' => $todayCalls,
                'upcoming_appointments' => $upcomingAppointments,
                'average_duration_seconds' => round($avgDuration),
                'ai_conversion_rate' => $conversionRate,
            ],
        ]);
    }

    public function charts()
    {
        // Daily call distribution for last 7 days
        $days = [];
        $callsData = [];
        $apptsData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('M d');
            $days[] = $dateStr;

            $callsData[] = Call::whereDate('created_at', $date)->count();
            $apptsData[] = Appointment::whereDate('created_at', $date)->count();
        }

        // Outcome breakdown
        $outcomes = CallOutcome::selectRaw('outcome, count(*) as total')
            ->groupBy('outcome')
            ->pluck('total', 'outcome')
            ->toArray();

        return response()->json([
            'success' => true,
            'charts' => [
                'labels' => $days,
                'calls_per_day' => $callsData,
                'appointments_per_day' => $apptsData,
                'outcomes' => $outcomes,
            ],
        ]);
    }
}
