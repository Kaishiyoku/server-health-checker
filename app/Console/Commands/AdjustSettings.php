<?php

namespace App\Console\Commands;

use App\Enums\SettingType;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class AdjustSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adjust health checker settings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $settings = Setting::all();

        $settings->each(function (Setting $setting) {
            $availableMethods = collect([
                SettingType::String => fn(Setting $adjustedSetting) => $this->ask($adjustedSetting->description, $adjustedSetting->string_value),
                SettingType::Bool => fn(Setting $adjustedSetting) => $this->confirm($adjustedSetting->description, $adjustedSetting->bool_value ?? false),
            ]);

            $valueField = $setting->type->value . '_value';
            $value = $availableMethods->get($setting->type->value)($setting);

            Setting::whereKey($setting->key->value)->update([
                $valueField => $value,
            ]);
        });

        Cache::delete('health_checks');

        Artisan::call(RunHealthChecks::class);

        $this->line('Settings updated.');

        return Command::SUCCESS;
    }
}
