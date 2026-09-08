<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthCheckCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run health checks for database and redis connections.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        jsonLogger('Health check command started.');
        $dbConnection = false;
        $redisConnection = false;
        // Database health check
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
           jsonLogger('Database connection: OK');
            $dbConnection = true;
        } catch (Throwable $exception) {
            jsonLogger(['message' => 'Database connection: FAILED', 'error' => $exception->getMessage()], 'error');
            $dbConnection = false;
        }

        // Redis health check
        try {
            $response = Redis::connection()->ping();
            $responseValue = is_object($response) ? (string) $response : $response;
            if (is_string($responseValue) && in_array(strtoupper(trim($responseValue)), ['PONG', '+PONG', 'OK'], true)) {
                jsonLogger(['message' => 'Redis connection: OK']);
                $redisConnection = true;
            } else {
                throw new \RuntimeException('Unexpected Redis ping response: ' . var_export($response, true));
            }
        } catch (Throwable $exception) {
            jsonLogger(['message' => 'Redis connection: FAILED', 'error' => $exception->getMessage()], 'error');
            $redisConnection = false;
        }

        if ($dbConnection && $redisConnection) {
            jsonLogger(['message' => 'Health check completed: PASSED']);
            return Command::SUCCESS;
        }

        jsonLogger(['message' => 'Health check completed: FAILED'], 'error');
        return Command::FAILURE;
    }
}
