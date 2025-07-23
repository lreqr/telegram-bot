<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramMessageJob;
use App\Services\TaskMessageFormatter;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            $client = new Client();
            $response = $client->request('GET', $url);
            $body = $response->getBody()->getContents();
            $data = json_decode($body);

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
