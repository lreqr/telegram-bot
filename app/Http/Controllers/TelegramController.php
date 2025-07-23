<?php

namespace App\Http\Controllers;

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
            $user = [
                'telegram_id' => $chatId,
                'name' => $update['message']['chat']['first_name'],
                'subscribed' => true,
            ];
//            User::updateOrCreate(
//                ['id' => $chatId],
//                [
//                    'name' => $update['message']['chat']['first_name'],
//                    'subscribed' => true,
//                ]);
            $userDb = new User($user);
            $userDb->save();
            return response()->json($userDb->toArray(), 201);
        } elseif ($text === '/stop') {
            User::updateOrCreate(
                ['id' => $chatId],
                [
                    'name' => $update['message']['chat']['first_name'],
                    'subscribed' => false,
                ]);
        }
    }
}
