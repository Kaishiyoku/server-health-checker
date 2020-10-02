<?php

use App\Enums\Setting;
use App\Enums\SettingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('type');
            $table->string('string_value')->nullable();
            $table->boolean('bool_value')->nullable();
            $table->string('description');
        });

        /*** @var \Illuminate\Database\DatabaseManager $db */
        $db = app('db');

        $teamspeak_server = $db->table('teamspeak_servers')->first();

        $teamspeak_server_available = !!$teamspeak_server;
        $teamspeak_server_name = $teamspeak_server ? $teamspeak_server->name : null;
        $teamspeak_server_password = $teamspeak_server ? $teamspeak_server->password : null;

        $db->table('settings')->insert([
            'key' => Setting::TeamSpeakServerAvailable,
            'type' => SettingType::Bool,
            'bool_value' => $teamspeak_server_available,
            'description' => 'Is a TeamSpeak server available?',
        ]);

        $db->table('settings')->insert([
            'key' => Setting::TeamSpeakServerName(),
            'type' => SettingType::String,
            'string_value' => $teamspeak_server_name,
            'description' => 'The name of the TeamSpeak server.',
        ]);

        $db->table('settings')->insert([
            'key' => Setting::TeamSpeakServerPassword(),
            'type' => SettingType::String,
            'string_value' => $teamspeak_server_password,
            'description' => 'The password of the TeamSpeak server.',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
