<?php

namespace Tests\Feature;

use App\Models\ChatbotConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_chatbot_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('chatbot.message'), [
            'message' => 'Tôi muốn đặt sân',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['conversation_id', 'message' => ['id', 'sender', 'message', 'intent']]);

        $this->assertDatabaseHas('chatbot_conversations', [
            'user_id' => $user->id,
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('chatbot_messages', [
            'sender' => 'user',
            'message' => 'Tôi muốn đặt sân',
        ]);

        $this->assertDatabaseHas('chatbot_messages', [
            'sender' => 'bot',
            'intent' => 'booking_help',
        ]);
    }

    public function test_user_can_reset_own_chatbot_conversation(): void
    {
        $user = User::factory()->create();
        $conversation = ChatbotConversation::create([
            'user_id' => $user->id,
            'status' => 'open',
            'locale' => 'vi',
            'source' => 'web',
        ]);

        $response = $this->actingAs($user)->postJson(route('chatbot.reset'), [
            'conversation_id' => $conversation->id,
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('chatbot_conversations', [
            'id' => $conversation->id,
            'status' => 'closed',
        ]);
    }
}
