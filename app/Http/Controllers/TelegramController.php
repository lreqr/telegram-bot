<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        \Log::info('Incoming Telegram update:', $request->all());

        $update = $request->all();

        if (!isset($update['message']['chat']['id'])) {
            return response()->json(['status' => 'no chat id']);
        }

        $chatId = $update['message']['chat']['id'];
        $text   = $update['message']['text'] ?? '';

        if ($text === '/start') {
            User::updateOrCreate(
                ['telegram_id' => $chatId],
                [
                    'name'       => $update['message']['chat']['first_name'],
                    'subscribed' => true,
                ]);

            $this->sendMessage($chatId, __('telegram.user.start'));
        } elseif ($text === '/stop') {
            User::updateOrCreate(
                ['id' => $chatId],
                [
                    'name'       => $update['message']['chat']['first_name'],
                    'subscribed' => false,
                ]);

            $this->sendMessage($chatId, __('telegram.user.stop'));
        }
    }

    public function sendMessage(int $chatId, string $text): void
    {
        $url = 'https://api.telegram.org/bot' . config('services.telegram.token') . '/sendMessage';

        $response = Http::post($url, [
            'chat_id' => $chatId,
            'text'    => $text
        ]);

        \Log::info('Telegram raw response: ' . $response->body());

        \Log::info('Telegram JSON:', $response->json());

    }
}
