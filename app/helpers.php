<?php

use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
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
