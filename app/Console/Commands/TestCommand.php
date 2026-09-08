<?php

namespace App\Console\Commands;

use App\Services\TracerService;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\TraceFlags;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-command';

    /**
     * The console command description.
     *j
     * @var string
     */
    protected $description = 'Command description';

    private $client;

    public function __construct()
    {
        parent::__construct();
        $stack = HandlerStack::create();
        $tracerService = app(TracerService::class);
        $stack->push($tracerService->injectTracingMiddleware(), 'open_telemetry');
        $this->client = new Client(['handler' => $stack]);
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // $requestUri = "https://svcs-smscast-api.wlink.com.np/v1/smscast/branch/branch_list";
            $requestUri = "http://ftth-gw-service.uat.wlink.com.np/onu-status-service/status/pramodk_home";

            $response = $this->client->get($requestUri, [
                'headers' => [
                    'Authorization' => 'Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJyYWRpcC5kaGFrYWwiLCJpYXQiOjE3NDIyMTUzMDcsImF1ZCI6ImNvcnBvcmF0ZS1zdXBwb3J0LndsaW5rLmNvbS5ucCIsImlzcyI6ImZ0dHgtZ2F0ZXdheS1zZXJ2aWNlLndsaW5rLmNvbS5ucCJ9.wGh8cROIafwJUNGT2YE6WV00QEl8Bbz8lOqGEGqmXDoQY82beeidX_wv89Xm5iLPWtg1NgLHArXwbvED32RxsXn8IrDP_zAVJLaf37RrD5cPxMqH4FcdKVvxywwmjBlFA-lwWWqbau2kxbebxzrBXX_Gx_EYYRAc-5x-YacWbBQwILf8rNyL23BFdquz9rTJo13e8MkFfVGfZCeVDq-F83JUSpXk95nU3943jRw1_B6IOJcH1kVLXF9xDlZUOM-cYaa-yWRgqEhuaFaPeVNM0VytmMMvB-A-ltfu1fdy9ZPfFHKuWq2BHyYsy0lzei16eut2ks_54jnH2T3aJo_uB27xxWX0LWrkYV0KJixdSWogVYUm9oML0rF5nI3dYmYs5rzFC0Hcx3Xups_BvrcV4hfpTaMO1-Sw57hRifORcJjG39NIHtYTkiaN4nw4QOXK7lJtF74zKWoka4XVrgw8PwS-EUlXe3oMgjsmXUGnBBh1ZyVA1HoR-8xcccDOHj7RRMz26vNLoFbfTrxZmam-APoKGPc1aHhox9FfipbCBpX0LMktU3vcbZAcjP3qLTEz9_jwtzWl-Ag8QdlBeo8AwIeR2gOLnexSMjVQRsrCtXxhoioI8IvdeUx0RzysgW-PJ4jIVoKJ1fZ6WWaiTEv4ZNKkE66aAMGdESYSX6lNkGk',
                ],
            ]);
            if ($response->getStatusCode() === 200) {
                Log::info(json_decode($response->getBody(), true));
            }
        } catch (\Throwable $th) {
            Log::error(json_encode(['method' => __METHOD__, 'message' => $th->getMessage()]));
            return [];
        }
    }
}
