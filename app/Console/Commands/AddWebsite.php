<?php

namespace App\Console\Commands;

use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Http;

class AddWebsite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health-checker:add-website';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add website to be health checked periodically';

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

        $url = $this->ask('Url');

        $isUrlHealthy = isUrlHealthy($url);

        $this->db->table('websites')->insert([
            'url' => $url,
            'is_healthy' => $isUrlHealthy,
            'created_at' => getCurrentDateAsString(),
            'updated_at' => getCurrentDateAsString(),
        ]);

        $cache->delete('health_checks');

        $this->line('Website added.');
    }
}
