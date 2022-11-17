<?php

namespace App\Console\Commands;

use App\Enums\Setting as SettingEnum;
use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use function Termwind\{render};

class RunHealthChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:run {--c|cli}';

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
        // $this->option('cli')

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

        if ($this->option('cli')) {
            $this->renderHealthChecks($healthChecks);
        } else {
            $this->line(json_encode($healthChecks, JSON_PRETTY_PRINT));
        }

        return Command::SUCCESS;
    }

    private function renderHealthChecks(array $healthChecks)
    {
        $databaseHealthText = $healthChecks['database'] ? 'Healthy' : 'Not healthy';
        $databaseHealthClass = $healthChecks['database'] ? 'text-green-500' : 'text-red-500';

        $redisHealthText = $healthChecks['redis'] ? 'Healthy' : 'Not healthy';
        $redisHealthClass = $healthChecks['redis'] ? 'text-green-500' : 'text-red-500';

        $teamspeakServerHealthText = $healthChecks['teamspeak_server'] ? 'Healthy' : 'Not healthy';
        $teamspeakServerHealthClass = $healthChecks['teamspeak_server'] ? '' : 'text-red-500';
        if ($healthChecks['teamspeak_server'] === null) {
            $teamspeakServerHealthText = 'Not configured';
            $teamspeakServerHealthClass = 'text-gray-400';
        }

        $websitesHtml = $healthChecks['websites']->map(function (bool $isHealthy, string $url) {
            $healthyText = $isHealthy ? 'Healthy' : 'Not healthy';
            $healthyClass = $isHealthy ? 'text-green-500' : 'text-red-500';

            return <<<HTML
                    <div class="flex {$healthyClass}">
                        <span>{$url}­</span>
                        <span class="flex-1 content-repeat-['.']">.</span>
                        <span class="uppercase">­{$healthyText}</span>
                    </div>
                HTML;
        })->join('');

        render(<<<HTML
            <div class="mx-2 my-1">
                <div class="mb-1">Health check performed at {$healthChecks['check_performed_at']}</div>
                <div class="flex">
                    <span>Database­</span>
                    <span class="flex-1 content-repeat-['.']"></span>
                    <span class="uppercase {$databaseHealthClass}">­{$databaseHealthText}</span>
                </div>
                <div class="flex">
                    <span>Redis­</span>
                    <span class="flex-1 content-repeat-['.']"></span>
                    <span class="uppercase {$redisHealthClass}">­{$redisHealthText}</span>
                </div>
                <div class="flex">
                    <span>Teamspeak server­</span>
                    <span class="flex-1 content-repeat-['.']"></span>
                    <span class="uppercase {$teamspeakServerHealthClass}">­{$teamspeakServerHealthText}</span>
                </div>

                {$websitesHtml}
            </div>
        HTML);
    }
}
