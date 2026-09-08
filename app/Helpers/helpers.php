<?php

use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


if (! function_exists('correlationId')) {
    function correlationId(): ?string
    {
        return Context::get('correlation_id');
    }
}

if (! function_exists('jsonLogger')) {
    function jsonLogger($log, $log_type = 'info')
    {
        $correlationId = correlationId();

        if ($log instanceof \Throwable) {
            $logData = [
                'error'   => get_class($log),
                'message' => $log->getMessage(),
                'code'    => $log->getCode(),
                'file'    => $log->getFile(),
                'line'    => $log->getLine(),
                'trace'   => collect($log->getTrace())->take(5),
            ];
        } elseif (is_array($log)) {
            $logData = [];

            foreach ($log as $key => $value) {
                if ($value instanceof \Throwable) {
                    $logData[$key] = [
                        'error'   => get_class($value),
                        'message' => $value->getMessage(),
                        'code'    => $value->getCode(),
                        'file'    => $value->getFile(),
                        'line'    => $value->getLine(),
                        'trace'   => collect($value->getTrace())->take(5),
                    ];
                } else {
                    $logData[$key] = $value;
                }
            }
        } else {
            $logData = ['message' => $log];
        }

        $logData = array_merge([
            'uuid' => $correlationId,
        ], $logData);

        Log::{$log_type}(json_encode($logData));
    }
}
