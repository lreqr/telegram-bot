<?php

namespace Tests\Unit;

use App\Jobs\SendTelegramMessageJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Testing\Fakes\Http as HttpFake;
use Tests\TestCase;

class NotifyTasksCommandTest extends TestCase
{
    public function test_handle_no_users_subscribed_does_nothing()
    {
        DB::table('users')->truncate();
        Bus::fake();

        $this->artisan('app:notify-tasks')->assertExitCode(0);
        Bus::assertNothingDispatched();
    }

    public function test_handle_filters_tasks_and_dispatches_jobs()
    {
        DB::table('users')->insert([ 'telegram_id' => 111, 'name' => 'Test', 'subscribed' => true ]);

        Http::fake([
            '*' => Http::response(json_encode([
                ['userId' => 1, 'completed' => false, 'title' => 'One'],
                ['userId' => 6, 'completed' => false, 'title' => 'Skip'],
                ['userId' => 2, 'completed' => true,  'title' => 'Done'],
            ]), 200),
        ]);

        Cache::flush();

        Bus::fake();

        $this->artisan('app:notify-tasks')->assertExitCode(0);

        Bus::assertDispatchedTimes(SendTelegramMessageJob::class, 1);
        Bus::assertDispatched(SendTelegramMessageJob::class, function ($job) {
            return $job->chatId === 111 && str_contains($job->text, 'One');
        });
    }

    public function test_handle_empty_tasks_returns_without_dispatch()
    {
        DB::table('users')->insert([ 'telegram_id' => 222, 'name' => 'NoTasks', 'subscribed' => true ]);

        Http::fake(['*' => Http::response(json_encode([]), 200)]);
        Cache::flush();
        Bus::fake();

        $this->artisan('app:notify-tasks')->assertExitCode(0);
        Bus::assertNothingDispatched();
    }
}
