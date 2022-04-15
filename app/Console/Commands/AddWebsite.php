<?php

namespace App\Console\Commands;

use App\Models\Website;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class AddWebsite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:add-website';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add website to be health checked periodically';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->ask('Url');

        $isUrlHealthy = isUrlHealthy($url);

        Website::create([
            'url' => $url,
            'is_healthy' => $isUrlHealthy,
        ]);

        Cache::delete('health_checks');

        Artisan::call(RunHealthChecks::class);

        $this->line('Website added.');

        return 0;
    }
}
