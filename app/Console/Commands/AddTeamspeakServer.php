<?php

namespace App\Console\Commands;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Artisan;

class AddTeamspeakServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health-checker:add-teamspeak-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add TeamSpeak server to be health checked periodically';

    /**
     * @var DatabaseManager
     */
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

        $name = $this->ask('Name');
        $port = $this->ask('Port');
        $password = $this->ask('Password');

        $isTeamspeakServerHealthy = isTeamspeakServerHealthy($port, $password);

        $this->db->table('teamspeak_servers')->insert([
            'name' => $name,
            'port' => $port,
            'password' => $password,
            'is_healthy' => $isTeamspeakServerHealthy,
            'created_at' => getCurrentDateAsString(),
            'updated_at' => getCurrentDateAsString(),
        ]);

        $cache->delete('health_checks');

        Artisan::call(RunHealthChecks::class);

        $this->line('TeamSpeak server added.');
    }
}
