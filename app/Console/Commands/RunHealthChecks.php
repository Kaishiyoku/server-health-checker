<?php

namespace App\Console\Commands;

use App\Enums\Setting as SettingEnum;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RunHealthChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run server health checks';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDatabaseHealthy = isDatabaseHealthy();
        $isRedisHealthy = isRedisHealthy();

        $websites = $isDatabaseHealthy ? Website::query()
            ->get(['url', 'is_healthy'])
            ->mapWithKeys(fn($website) => [$website->url => isUrlHealthy($website->url)]) : null;

        $isTeamspeakServerAvailable = getSettingValue(SettingEnum::TeamSpeakServerAvailable());
        $teamspeakServerName = getSettingValue(SettingEnum::TeamSpeakServerName());
        $teamspeakServerPassword = getSettingValue(SettingEnum::TeamSpeakServerPassword());

        $teamspeakServer = $isTeamspeakServerAvailable ? [
            $teamspeakServerName => isTeamspeakServerHealthy($teamspeakServerPassword),
        ] : null;

        $healthChecks = [
            'check_performed_at' => getCurrentDateAsString(),
            'database' => $isDatabaseHealthy,
            'redis' => $isRedisHealthy,
            'websites' => $websites,
            'teamspeak_server' => $teamspeakServer,
        ];

        Cache::put('health_checks', $healthChecks);

        $this->line(json_encode($healthChecks, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}
