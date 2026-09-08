<?php

namespace App\Services;

use GuzzleHttp\Middleware;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;

class TracerService
{
    public function injectTracingMiddleware(): callable
    {
        $otelStatus  = app('otelStatus');
        return Middleware::mapRequest(function ($request) use ($otelStatus) {
            if (!$otelStatus) {
                return $request;
            }

            $tracer = app('OtelTracer');
            $span = $tracer
                ->spanBuilder($request->getMethod() . ' ' . (string) $request->getUri())
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->startSpan();

            $span->setAttribute('http.method', $request->getMethod());
            $span->setAttribute('http.url', (string) $request->getUri());
            $span->setAttribute('http.headers', json_encode($request->getHeaders()));
            $span->setAttribute('http.body', $request->getBody()->getContents());

            $scope = $span->activate();

            try {
                $carrier = [];
                TraceContextPropagator::getInstance()->inject($carrier, null, Context::getCurrent());
                foreach ($carrier as $key => $value) {
                    $request = $request->withHeader($key, $value);
                }
            } finally {
                $span->end();       // Close span properly
                $scope->detach();   // Detach scope *only if* it’s still on top
            }

            return $request;
        });
    }
}
