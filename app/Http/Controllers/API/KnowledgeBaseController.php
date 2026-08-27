<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AIAgentFaq;
use App\Models\AIAgentKnowledge;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function index()
    {
        $knowledge = AIAgentKnowledge::with('aiAgent')->latest()->get();
        $faqs = AIAgentFaq::with('aiAgent')->latest()->get();

        return response()->json([
            'success' => true,
            'knowledge' => $knowledge,
            'faqs' => $faqs,
        ]);
    }

    public function storeKnowledge(Request $request)
    {
        $validated = $request->validate([
            'ai_agent_id' => 'nullable|exists:ai_agents,id',
            'category' => 'required|string',
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $item = AIAgentKnowledge::create($validated);

        return response()->json(['success' => true, 'item' => $item]);
    }

    public function storeFaq(Request $request)
    {
        $validated = $request->validate([
            'ai_agent_id' => 'nullable|exists:ai_agents,id',
            'question' => 'required|string',
            'answer' => 'required|string',
            'keywords' => 'nullable|array',
        ]);

        $faq = AIAgentFaq::create($validated);

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    public function destroyKnowledge($id)
    {
        AIAgentKnowledge::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function destroyFaq($id)
    {
        AIAgentFaq::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
