<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SiriSubscriptionVersion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('siri_subscriptions', function (Blueprint $table) {
            $table->char('version', 12)->after('name');
        });
        DB::table('siri_subscriptions')->update(['version' => '1.4']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('siri_subscriptions', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
}
