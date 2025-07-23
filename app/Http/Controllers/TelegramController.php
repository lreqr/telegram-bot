<?php

namespace App\Http\Controllers;

use App\Jobs\SendTelegramMessageJob;
use App\Models\User;
use Illuminate\Http\Request;

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

            SendTelegramMessageJob::dispatch($chatId, __('telegram.user.start'));
        } elseif ($text === '/stop') {
            User::updateOrCreate(
                ['telegram_id' => $chatId],
                [
                    'name'       => $update['message']['chat']['first_name'],
                    'subscribed' => false,
                ]);

            SendTelegramMessageJob::dispatch($chatId, __('telegram.user.stop'));
        }
    }

}
