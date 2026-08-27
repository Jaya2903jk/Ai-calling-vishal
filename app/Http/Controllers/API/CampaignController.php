<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignContact;
use App\Models\Customer;
use App\Services\AI\ConversationEngine;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::with(['aiAgent', 'contacts.customer'])->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'ai_agent_id' => 'required|exists:ai_agents,id',
            'call_purpose' => 'nullable|string',
            'customer_ids' => 'required|array',
        ]);

        $campaign = Campaign::create([
            'name' => $request->name,
            'ai_agent_id' => $request->ai_agent_id,
            'call_purpose' => $request->call_purpose ?? 'Outreach Campaign',
            'status' => 'draft',
            'total_contacts' => count($request->customer_ids),
        ]);

        foreach ($request->customer_ids as $cid) {
            CampaignContact::create([
                'campaign_id' => $campaign->id,
                'customer_id' => $cid,
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Campaign created successfully',
            'campaign' => $campaign->load('contacts'),
        ]);
    }

    public function start($id, ConversationEngine $engine)
    {
        $campaign = Campaign::with('contacts.customer')->findOrFail($id);
        $campaign->update(['status' => 'running']);

        $completed = 0;
        $conversions = 0;

        foreach ($campaign->contacts as $contact) {
            if ($contact->status === 'pending') {
                $call = $engine->executeFullCallPipeline(
                    $contact->customer,
                    null,
                    $campaign->ai_agent_id,
                    $campaign->call_purpose
                );

                $contact->update([
                    'status' => 'called',
                    'call_id' => $call->id,
                ]);

                $completed++;
                if ($call->outcome && $call->outcome->outcome === 'Appointment Booked') {
                    $conversions++;
                }
            }
        }

        $campaign->update([
            'status' => 'completed',
            'completed_calls' => $completed,
            'conversions' => $conversions,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign completed processing successfully',
            'campaign' => $campaign->fresh(),
        ]);
    }

    public function pause($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->update(['status' => 'paused']);

        return response()->json(['success' => true, 'campaign' => $campaign]);
    }

    public function stop($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->update(['status' => 'cancelled']);

        return response()->json(['success' => true, 'campaign' => $campaign]);
    }
}
