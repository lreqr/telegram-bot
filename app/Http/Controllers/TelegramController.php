<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        \Log::info('Incoming Telegram update:', $request->all());
    }
}
