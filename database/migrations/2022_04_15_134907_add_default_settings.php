<?php

use App\Enums\Setting as SettingEnum;
use App\Enums\SettingType;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Setting::create([
            'key' => SettingEnum::TeamSpeakServerAvailable(),
            'type' => SettingType::Bool(),
            'bool_value' => false,
            'description' => 'Is a TeamSpeak server available?',
        ]);

        Setting::create([
            'key' => SettingEnum::TeamSpeakServerName(),
            'type' => SettingType::String(),
            'string_value' => null,
            'description' => 'The name of the TeamSpeak server.',
        ]);

        Setting::create([
            'key' => SettingEnum::TeamSpeakServerPassword(),
            'type' => SettingType::String(),
            'string_value' => null,
            'description' => 'The password of the TeamSpeak Server.',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
