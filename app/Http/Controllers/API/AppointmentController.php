<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\Appointment\AppointmentAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    protected AppointmentAvailabilityService $availabilityService;

    public function __construct(AppointmentAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function index(Request $request)
    {
        $query = Appointment::with(['customer', 'service', 'user', 'call']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $appointments = $query->orderBy('date', 'desc')->orderBy('start_time', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $appointments,
        ]);
    }

    public function availableSlots(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $date = $request->date;
        $serviceId = $request->service_id;

        $slots = $this->availabilityService->getAvailableSlots($date, $serviceId);

        return response()->json([
            'success' => true,
            'date' => $date,
            'available_slots' => $slots,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'service_id' => 'nullable|exists:services,id',
            'notes' => 'nullable|string',
            'booking_source' => 'nullable|string',
        ]);

        $appointment = $this->availabilityService->bookAppointment(
            $request->customer_id,
            $request->date,
            $request->start_time,
            $request->service_id,
            $request->booking_source ?? 'Employee',
            null,
            $request->notes
        );

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully',
            'appointment' => $appointment->load(['customer', 'service']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($request->only(['status', 'date', 'start_time', 'end_time', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Appointment updated successfully',
            'appointment' => $appointment->fresh(['customer', 'service']),
        ]);
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled/deleted',
        ]);
    }
}
