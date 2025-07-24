<?php

namespace Tests\Feature;

use App\Jobs\SendTelegramMessageJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class TelegramControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_command_creates_and_subscribes_user_and_dispatches_job()
    {
        Bus::fake();

        $payload = [
            'message' => [
                'chat' => [
                    'id' => 12345,
                    'first_name' => 'Alice',
                ],
                'text' => '/start',
            ],
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'telegram_id' => 12345,
            'name' => 'Alice',
            'subscribed' => true,
        ]);

        Bus::assertDispatched(SendTelegramMessageJob::class, function ($job) {
            return $job->chatId === 12345;
        });
    }

    public function test_stop_command_updates_and_unsubscribes_user_and_dispatches_job()
    {
        Bus::fake();

        $payload = [
            'message' => [
                'chat' => [
                    'id' => 54321,
                    'first_name' => 'Bob',
                ],
                'text' => '/stop',
            ],
        ];

        $response = $this->postJson('/api/telegram/webhook', $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'telegram_id' => 54321,
            'name' => 'Bob',
            'subscribed' => false,
        ]);

        Bus::assertDispatched(SendTelegramMessageJob::class, function ($job) {
            return $job->chatId === 54321;
        });
    }

    public function test_invalid_payload_returns_no_chat_id()
    {
        $response = $this->postJson('/api/telegram/webhook', ['foo' => 'bar']);
        $response->assertJson(['status' => 'no chat id']);
    }
}
