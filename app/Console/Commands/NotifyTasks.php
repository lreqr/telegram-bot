<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramMessageJob;
use App\Services\TaskMessageFormatter;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class NotifyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = DB::table('users')
            ->where('subscribed', '=', true)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        $url = 'https://jsonplaceholder.typicode.com/todos';
        try {

            $data = Cache::remember('tasks_from_placeholder', now()->addMinutes(10), function () use ($url) {
                $response = Http::get($url);
                return json_decode($response->body());
            });

            if (empty($data)) {
                return;
            }

            //Filter tasks
            $data = array_filter($data, function ($item) {
                return $item->completed === false && $item->userId <= 5;
            });

            if (empty($data)) {
                return;
            }

            $message = TaskMessageFormatter::format($data);

            foreach ($users as $user) {
                //Send messages to users
                SendTelegramMessageJob::dispatch($user->telegram_id, $message);
            }

        } catch (\Exception $e) {
            \Log::error('NotifyTasks error: ' . $e->getMessage());
        }
    }

}
