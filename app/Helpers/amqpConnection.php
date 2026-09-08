<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;

if (! function_exists('amqpTmConnection')) {
    function amqpTmConnection()
    {
        static $tmConnection = null;
        if ($tmConnection) {
            try {
                // Quick check if connection is still alive
                if ($tmConnection->isConnected()) {
                    return $tmConnection;
                }
            } catch (\Exception $e) {
                // Connection is dead, will create new one
                $tmConnection = null;
            }
        }
        $tmConnection = new AMQPStreamConnection(
            $host = env('TM_RABBITMQ_HOST'),
            $port = env('TM_RABBITMQ_PORT', 5672),
            $user = env('TM_RABBITMQ_USER'),
            $password = env('TM_RABBITMQ_PASSWORD'),
            $vhost = env('TM_RABBITMQ_VHOST'),
            $insist = false,
            $login_method = 'AMQPLAIN',
            $login_response = null,
            $locale = 'en_US',
            $connection_timeout = env('RABBITMQ_CONNECTION_TIMEOUT', 10.0),
            $read_write_timeout = env('RABBITMQ_READ_WRITE_TIMEOUT', 130.0), // Increased for heartbeat
            $context = null,
            $keepalive = true,
            $heartbeat = env('RABBITMQ_HEARTBEAT', 60)
        );
        return $tmConnection;
    }
}

if (! function_exists('amqpCsConnection')) {
    function amqpCsConnection()
    {
        static $csConnection = null;
        if ($csConnection) {
            try {
                // Quick check if connection is still alive
                if ($csConnection->isConnected()) {
                    return $csConnection;
                }
            } catch (\Exception $e) {
                // Connection is dead, will create new one
                $csConnection = null;
            }
        }
        $csConnection = new AMQPStreamConnection(
            $host = env('TM_RABBITMQ_HOST'),
            $port = env('TM_RABBITMQ_PORT', 5672),
            $user = env('TM_RABBITMQ_USER'),
            $password = env('TM_RABBITMQ_PASSWORD'),
            $vhost = env('TM_RABBITMQ_VHOST'),
            $insist = false,
            $login_method = 'AMQPLAIN',
            $login_response = null,
            $locale = 'en_US',
            $connection_timeout = env('RABBITMQ_CONNECTION_TIMEOUT', 10.0),
            $read_write_timeout = env('RABBITMQ_READ_WRITE_TIMEOUT', 130.0), // Increased for heartbeat
            $context = null,
            $keepalive = true,
            $heartbeat = env('RABBITMQ_HEARTBEAT', 60)
        );
        return $csConnection;
    }
}
