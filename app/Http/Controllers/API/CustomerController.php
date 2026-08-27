<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\CustomerNote;
use App\Services\AI\ConversationEngine;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['service', 'assignedEmployee']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('lead_status', $request->status);
        }

        $customers = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email',
            'gender' => 'nullable|string',
            'dob' => 'nullable|date',
            'location' => 'nullable|string',
            'source' => 'nullable|string',
            'lead_status' => 'nullable|string',
            'interested_service_id' => 'nullable|exists:services,id',
            'notes' => 'nullable|string',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        $customer = Customer::create($validated);

        CustomerActivity::create([
            'customer_id' => $customer->id,
            'user_id' => $request->user()?->id,
            'activity_type' => 'CUSTOMER_CREATED',
            'description' => 'Customer profile was created in the platform.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'customer' => $customer->load(['service', 'assignedEmployee']),
        ]);
    }

    public function show($id)
    {
        $customer = Customer::with([
            'service',
            'assignedEmployee',
            'calls.transcript',
            'calls.recording',
            'calls.summary',
            'calls.outcome',
            'calls.aiAgent',
            'appointments.service',
            'notesList.user',
            'activities.user',
            'followUps',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string',
            'email' => 'nullable|email',
            'gender' => 'nullable|string',
            'location' => 'nullable|string',
            'lead_status' => 'nullable|string',
            'interested_service_id' => 'nullable|exists:services,id',
            'notes' => 'nullable|string',
            'assigned_user_id' => 'nullable|exists:users,id',
        ]);

        $customer->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'customer' => $customer->load(['service', 'assignedEmployee']),
        ]);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully',
        ]);
    }

    public function addNote(Request $request, $id)
    {
        $request->validate(['note' => 'required|string']);

        $customer = Customer::findOrFail($id);
        $note = CustomerNote::create([
            'customer_id' => $customer->id,
            'user_id' => $request->user()?->id,
            'note' => $request->note,
        ]);

        return response()->json([
            'success' => true,
            'note' => $note->load('user'),
        ]);
    }

    public function startAICall(Request $request, $id, ConversationEngine $engine)
    {
        $customer = Customer::findOrFail($id);
        $purpose = $request->input('call_purpose', 'Enquiry Follow-Up');
        $agentId = $request->input('ai_agent_id');

        $call = $engine->executeFullCallPipeline(
            $customer,
            $request->user()?->id,
            $agentId,
            $purpose
        );

        return response()->json([
            'success' => true,
            'message' => 'AI Call initiated and completed successfully!',
            'call' => $call,
        ]);
    }
}
