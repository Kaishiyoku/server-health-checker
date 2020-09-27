<?php

namespace App\Console\Commands;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;

class RunHealthChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health-checker:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run server health checks';

    protected $db;

    /**
     * Create a new command instance.
     *
     * @param DatabaseManager $db
     */
    public function __construct(DatabaseManager $db)
    {
        parent::__construct();

        $this->db = $db;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*** @var CacheRepository $cache */
        $cache = app()->make('cache.store');

        $isDatabaseHealthy = isDatabaseHealthy($this->db);
        $isRedisHealthy = isRedisHealthy();

        $websites = $isDatabaseHealthy ? $this->db
            ->table('websites')
            ->get(['url', 'is_healthy'])
            ->mapWithKeys(function ($website) {
                return [$website->url => isUrlHealthy($website->url)];
            }) : null;

        $teamspeakServers = $isDatabaseHealthy ? $this->db
            ->table('teamspeak_servers')
            ->get(['name', 'port', 'password', 'is_healthy'])
            ->mapWithKeys(function ($teamspeakServer) {
                return [$teamspeakServer->name => isTeamspeakServerHealthy($teamspeakServer->port, $teamspeakServer->password)];
            }) : null;

        $healthChecks = [
            'check_performed_at' => getCurrentDateAsString(),
            'database' => $isDatabaseHealthy,
            'redis' => $isRedisHealthy,
            'websites' => $websites,
            'teamspeak_servers' => $teamspeakServers,
        ];

        $cache->put('health_checks', $healthChecks);

        $this->line(json_encode($healthChecks, JSON_PRETTY_PRINT));
    }
}
