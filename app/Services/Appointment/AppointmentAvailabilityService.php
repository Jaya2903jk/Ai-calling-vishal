<?php

namespace App\Services\Appointment;

use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\Customer;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class AppointmentAvailabilityService
{
    /**
     * Get available appointment slots for a specific date and service.
     */
    public function getAvailableSlots(string $date, ?int $serviceId = null): array
    {
        $service = $serviceId ? Service::find($serviceId) : Service::first();
        $duration = $service ? $service->duration_minutes : 30;

        // Fetch pre-configured available slots for date
        $dbSlots = AppointmentSlot::where('date', $date)
            ->where('is_booked', false)
            ->get();

        if ($dbSlots->count() > 0) {
            return $dbSlots->map(fn($s) => [
                'id' => $s->id,
                'start_time' => Carbon::parse($s->start_time)->format('h:i A'),
                'end_time' => Carbon::parse($s->end_time)->format('h:i A'),
                'raw_start' => $s->start_time,
                'raw_end' => $s->end_time,
            ])->toArray();
        }

        // Generate standard business slots (e.g., 10 AM to 6 PM) excluding booked appointments
        $standardTimes = ['10:00:00', '11:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00'];
        $existingBookings = Appointment::where('date', $date)
            ->whereIn('status', ['confirmed', 'pending'])
            ->pluck('start_time')
            ->map(fn($t) => Carbon::parse($t)->format('H:i:s'))
            ->toArray();

        $available = [];
        foreach ($standardTimes as $t) {
            if (!in_array($t, $existingBookings)) {
                $available[] = [
                    'id' => null,
                    'start_time' => Carbon::parse($t)->format('g:i A'),
                    'end_time' => Carbon::parse($t)->addMinutes($duration)->format('g:i A'),
                    'raw_start' => $t,
                    'raw_end' => Carbon::parse($t)->addMinutes($duration)->format('H:i:s'),
                ];
            }
        }

        return $available;
    }

    /**
     * Securely book an appointment using DB transaction & pessimistic locking.
     */
    public function bookAppointment(
        int $customerId,
        string $date,
        string $startTime,
        ?int $serviceId = null,
        string $bookingSource = 'AI Call',
        ?int $callId = null,
        ?string $notes = null
    ): Appointment {
        return DB::transaction(function () use ($customerId, $date, $startTime, $serviceId, $bookingSource, $callId, $notes) {
            $parsedDate = Carbon::parse($date)->format('Y-m-d');
            $parsedStartTime = Carbon::parse($startTime)->format('H:i:s');
            $service = $serviceId ? Service::find($serviceId) : Service::first();
            $duration = $service ? $service->duration_minutes : 30;
            $parsedEndTime = Carbon::parse($startTime)->addMinutes($duration)->format('H:i:s');

            // Check existing appointments with lock
            $conflict = Appointment::where('date', $parsedDate)
                ->where('start_time', $parsedStartTime)
                ->whereIn('status', ['confirmed', 'pending'])
                ->lockForUpdate()
                ->first();

            if ($conflict) {
                throw new Exception("Slot {$parsedStartTime} on {$parsedDate} has already been booked by another customer.");
            }

            // Create appointment
            $appointment = Appointment::create([
                'customer_id' => $customerId,
                'user_id' => Customer::find($customerId)?->assigned_user_id ?? 1,
                'service_id' => $service?->id,
                'date' => $parsedDate,
                'start_time' => $parsedStartTime,
                'end_time' => $parsedEndTime,
                'status' => 'confirmed',
                'booking_source' => $bookingSource,
                'notes' => $notes ?? 'Booked via AI Call Engine',
                'call_id' => $callId,
            ]);

            // Update customer status
            Customer::where('id', $customerId)->update([
                'lead_status' => 'Appointment Booked',
            ]);

            // Mark slot as booked if explicit slot record exists
            AppointmentSlot::where('date', $parsedDate)
                ->where('start_time', $parsedStartTime)
                ->update(['is_booked' => true]);

            return $appointment;
        });
    }
}
