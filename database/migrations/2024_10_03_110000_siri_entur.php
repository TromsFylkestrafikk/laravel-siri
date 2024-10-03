<?php

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
        Schema::table('siri_sx_info_link', function (Blueprint $table) {
            $table->renameColumn('url', 'uri');
        });

        Schema::table('siri_sx_pt_situation', function (Blueprint $table) {
            $table->text('advice')->nullable()->comment("Textual advice on how a passenger should react/respond to the situation")->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('siri_sx_info_link', function (Blueprint $table) {
            $table->renameColumn('uri', 'url');
        });
    }
};
