<?php

use App\Enums\Setting as SettingEnum;
use App\Enums\SettingType;
use App\Models\Setting;
use Carbon\Carbon;
use GameQ\GameQ;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Predis\Client;
use Predis\Connection\ConnectionException;

if (!function_exists('isUrlHealthy')) {
    function isUrlHealthy(string $url): bool
    {
        try {
            return Http::get($url)->ok();
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('isTeamspeakServerHealthy')) {
    function isTeamspeakServerHealthy(?string $password = null): bool
    {
        // if the TeamSpeak server has a password we are only able to check if the server is running by opening a TCP socket
        if ($password) {
            try {
                $socket = fsockopen('127.0.0.1', 10011, $errno, $errstr, 2);
                fclose($socket);

                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        $serverAddress = '127.0.0.1:9987';

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
    function getCurrentDateAsString(): string
    {
        return Carbon::now()->toDateTimeString();
    }
}

if (!function_exists('isDatabaseHealthy')) {
    function isDatabaseHealthy(): bool
    {
        try {
            DB::table('websites')->count();

            return true;
        } catch (QueryException $e) {
            return false;
        }
    }
}

if (!function_exists('isRedisHealthy')) {
    function isRedisHealthy(): bool
    {
        try {
            $redisPassword = config('database.redis.default.password');
            $client = new Client();

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

if (!function_exists('getSettingValue')) {
    /**
     * @param SettingEnum $setting
     * @return mixed
     */
    function getSettingValue(SettingEnum $setting)
    {
        $dbSetting = Setting::firstWhere('key', $setting->value);

        $casts = collect([
            SettingType::Bool => fn($value) => (bool) $value,
        ]);

        $identityFn = function () { return fn($value) => $value; };

        return $casts->get($dbSetting->type->value, $identityFn)($dbSetting->{$dbSetting->type->value . '_value'});
    }
}
