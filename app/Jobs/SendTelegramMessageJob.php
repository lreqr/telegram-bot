<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;

class SendTelegramMessageJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $chatId;
    public string $text;

    public function __construct(int $chatId, string $text)
    {
        $this->chatId = $chatId;
        $this->text = $text;
    }

    public function handle(): void
    {
        $url = 'https://api.telegram.org/bot' . config('services.telegram.token') . '/sendMessage';
        try {
            $client = app(Client::class);
            $response = $client->request('POST', $url, [
                'form_params' => [
                    'chat_id' => $this->chatId,
                    'text'    => $this->text,
                ],
            ]);

            \Log::info('TG response status/body', [
                $response->getStatusCode(),
                $response->getBody(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Telegram error: ' . $e->getMessage());
        }
    }
}
