<?php

use Carbon\Carbon;
use GameQ\GameQ;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Predis\Connection\ConnectionException;

if (!function_exists('isUrlHealthy')) {
    /**
     * @param string $url
     * @return bool
     */
    function isUrlHealthy(string $url): bool
    {
        try {
            return Http::get($url)->ok();
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('isTeamspeakServerHealthy')) {
    function isTeamspeakServerHealthy(int $port, ?string $password = null): bool
    {
        // if the TeamSpeak server has a password we are only able to check if the server is running by opening a TCP socket
        if ($password) {
            try {
                $socket = fsockopen("45.83.107.36", 10011, $errno, $errstr, 2);
                fclose($socket);

                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        $serverAddress = '127.0.0.1:' . $port;

        $gameQ = new GameQ();
        $gameQ->addServer([
            'debug' => true,
            'type' => 'teamspeak3',
            'host' => $serverAddress,
            'options' => [
                'query_port' => 10011,
            ],
        ]);

        $data = $gameQ->process()[$serverAddress];

        return Arr::get($data, 'gq_online', false);
    }
}

if (!function_exists('getCurrentDateAsString')) {
    /**
     * @return string
     */
    function getCurrentDateAsString(): string
    {
        return Carbon::now()->toDateTimeString();
    }
}

if (!function_exists('isDatabaseHealthy')) {
    /**
     * @param DatabaseManager $db
     * @return bool
     */
    function isDatabaseHealthy(DatabaseManager $db): bool
    {
        try {
            $db->table('websites')->count();

            return true;
        } catch (QueryException $e) {
            return false;
        }
    }
}

if (!function_exists('isRedisHealthy')) {
    /**
     * @return bool
     */
    function isRedisHealthy(): bool
    {
        try {
            $redisPassword = env('REDIS_PASSWORD');
            $client = new \Predis\Client();

            if ($redisPassword) {
                $client->auth($redisPassword);
            }

            $client->dbsize();

            return true;
        } catch (ConnectionException $e) {
            return false;
        }
    }
}
