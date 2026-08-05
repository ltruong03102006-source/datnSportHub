<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChatbotConversation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminChatbotController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = ChatbotConversation::query()
            ->with(['user', 'messages' => fn($query) => $query->latest()->limit(1)])
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->string('status')))
            ->latest('last_message_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.chatbot.index', compact('conversations'));
    }
}
