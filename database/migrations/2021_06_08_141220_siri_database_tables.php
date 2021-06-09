<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SiriDatabaseTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('siri_subscriptions', function (Blueprint $table) {
            $table->char('id', 48)->primary();
            $table->char('type', 4);
            $table->char('heartbeat_interval', 16);
            $table->dateTime('last_communication')->useCurrent();
            $table->boolean('active')->default(true);
            $table->string('requestor_ref', 64);
            $table->string('subscription_address', 128);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('siri_subscriptions');
    }
}
