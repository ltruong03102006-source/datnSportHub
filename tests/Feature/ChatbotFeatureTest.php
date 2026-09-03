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

    public function test_user_cannot_send_empty_chatbot_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('chatbot.message'), [
            'message' => '   ',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_user_can_reset_latest_open_conversation_without_id(): void
    {
        $user = User::factory()->create();
        $conversation = ChatbotConversation::create([
            'user_id' => $user->id,
            'status' => 'open',
            'locale' => 'vi',
            'source' => 'web',
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson(route('chatbot.reset'));

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('chatbot_conversations', [
            'id' => $conversation->id,
            'status' => 'closed',
        ]);
    }

    public function test_bot_uses_recent_conversation_context_to_detect_intent(): void
    {
        $user = User::factory()->create();
        $conversation = ChatbotConversation::create([
            'user_id' => $user->id,
            'status' => 'open',
            'locale' => 'vi',
            'source' => 'web',
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'user_id' => $user->id,
            'sender' => 'user',
            'message' => 'Tôi muốn đặt sân',
        ]);

        $response = $this->actingAs($user)->postJson(route('chatbot.message'), [
            'message' => 'Mấy khung giờ còn trống nào?',
            'conversation_id' => $conversation->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message.intent', 'booking_help');
    }
}
