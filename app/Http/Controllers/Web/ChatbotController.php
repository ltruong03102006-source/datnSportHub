<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChatbotConversation;
use App\Services\ChatbotResponderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChatbotController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = ChatbotConversation::query()
            ->where('user_id', $request->user()->id)
            ->latest('last_message_at')
            ->paginate(10);

        return view('chatbot.index', compact('conversations'));
    }

    public function message(Request $request, ChatbotResponderService $responder): JsonResponse
    {
        abort_unless(config('chatbot.enabled'), 404);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:' . config('chatbot.max_user_message_length', 1000)],
            'conversation_id' => ['nullable', 'integer'],
        ]);

        $conversation = $this->conversation($request, $validated['conversation_id'] ?? null);
        $userMessage = trim($validated['message']);

        $conversation->messages()->create([
            'user_id' => $request->user()?->id,
            'sender' => 'user',
            'message' => $userMessage,
        ]);

        $reply = $responder->reply($userMessage);

        $botMessage = $conversation->messages()->create([
            'sender' => 'bot',
            'message' => $reply['message'],
            'intent' => $reply['intent'],
            'confidence' => $reply['confidence'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'message' => [
                'id' => $botMessage->id,
                'sender' => $botMessage->sender,
                'message' => $botMessage->message,
                'intent' => $botMessage->intent,
                'created_at' => $botMessage->created_at?->toIso8601String(),
            ],
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $conversationId = $request->integer('conversation_id');

        if ($conversationId) {
            ChatbotConversation::query()
                ->where('id', $conversationId)
                ->when($request->user(), fn($query) => $query->where('user_id', $request->user()->id))
                ->update(['status' => 'closed']);
        }

        return response()->json(['success' => true]);
    }

    private function conversation(Request $request, ?int $conversationId): ChatbotConversation
    {
        if ($conversationId) {
            $conversation = ChatbotConversation::query()
                ->where('id', $conversationId)
                ->when($request->user(), fn($query) => $query->where('user_id', $request->user()->id))
                ->first();

            if ($conversation) {
                return $conversation;
            }
        }

        return ChatbotConversation::create([
            'user_id' => $request->user()?->id,
            'session_id' => $request->session()->getId() ?: (string) Str::uuid(),
            'status' => 'open',
            'locale' => config('chatbot.default_locale', 'vi'),
            'source' => 'web',
            'last_message_at' => now(),
        ]);
    }
}
