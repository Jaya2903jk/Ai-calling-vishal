<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AIAgent;
use Illuminate\Http\Request;

class AIAgentController extends Controller
{
    public function index()
    {
        $agents = AIAgent::with(['knowledgeItems', 'faqs'])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $agents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'voice' => 'nullable|string',
            'language' => 'nullable|string',
            'business_name' => 'nullable|string',
            'greeting' => 'nullable|string',
            'personality' => 'nullable|string',
            'system_instructions' => 'nullable|string',
            'call_ending_message' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $agent = AIAgent::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'AI Voice Agent created successfully',
            'agent' => $agent,
        ]);
    }

    public function show($id)
    {
        $agent = AIAgent::with(['knowledgeItems', 'faqs'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'agent' => $agent,
        ]);
    }

    public function update(Request $request, $id)
    {
        $agent = AIAgent::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'voice' => 'nullable|string',
            'language' => 'nullable|string',
            'business_name' => 'nullable|string',
            'greeting' => 'nullable|string',
            'personality' => 'nullable|string',
            'system_instructions' => 'nullable|string',
            'call_ending_message' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $agent->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'AI Agent updated successfully',
            'agent' => $agent,
        ]);
    }

    public function destroy($id)
    {
        $agent = AIAgent::findOrFail($id);
        $agent->delete();

        return response()->json([
            'success' => true,
            'message' => 'AI Agent deleted successfully',
        ]);
    }
}
