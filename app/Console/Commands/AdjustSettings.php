<?php

namespace App\Console\Commands;

use App\Enums\SettingType;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Artisan;

class AdjustSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health-checker:settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adjust health checker settings';

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

        $settings = $this->db->table('settings')->get();

        $settings->each(function ($setting) {
            $availableMethods = collect([
                SettingType::String => function ($setting) { return $this->ask($setting->description, $setting->string_value); },
                SettingType::Bool => function ($setting) { return $this->confirm($setting->description, $setting->bool_value ?? false); },
            ]);

            $valueField = $setting->type . '_value';
            $value = $availableMethods->get($setting->type)($setting);

            $this->db->table('settings')->where('key', $setting->key)->update([
                $valueField => $value,
            ]);
        });

        $cache->delete('health_checks');

        Artisan::call(RunHealthChecks::class);

        $this->line('Settings updated.');
    }
}
