<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTeamspeakServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('teamspeak_servers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('teamspeak_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('port');
            $table->string('password')->nullable();
            $table->boolean('is_healthy');
            $table->timestamps();
        });
    }
}
