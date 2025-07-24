<?php

namespace Tests\Unit;

use App\Jobs\SendTelegramMessageJob;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class SendTelegramMessageJobTest extends TestCase
{
    public function test_handle_successful_request_logs_info()
    {
        Config::set('services.telegram.token', 'fake-token');

        $mockResponse = new Response(200, [], 'OK');
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('request')
            ->once()
            ->with('POST', Mockery::on(fn($url) => str_contains($url, 'fake-token')), [
                'form_params' => ['chat_id' => 999, 'text' => 'Hello'],
            ])
            ->andReturn($mockResponse);

        $job = new SendTelegramMessageJob(999, 'Hello');
        // Заменяем Client
        app()->instance(Client::class, $mockClient);

        Log::shouldReceive('info')->once();

        $job->handle();
    }

    public function test_handle_exception_logs_error()
    {
        Config::set('services.telegram.token', 'fake-token');

        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('request')->andThrow(new \Exception('Fail'));

        app()->instance(Client::class, $mockClient);

        Log::shouldReceive('error')->with(Mockery::on(fn($msg) => str_contains($msg, 'Fail')))->once();

        $job = new SendTelegramMessageJob(888, 'Err');
        $job->handle();
    }
}
