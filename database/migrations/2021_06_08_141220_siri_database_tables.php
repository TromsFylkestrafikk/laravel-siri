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
            $table->id();
            $table->char('channel', 4);
            $table->string('name', 128);
            $table->string('subscription_url', 128);
            $table->string('subscription_ref', 48)->unique();
            $table->string('requestor_ref', 64);
            $table->char('heartbeat_interval', 16);
            $table->boolean('active')->default(true);
            $table->integer('received')->default(0);
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
