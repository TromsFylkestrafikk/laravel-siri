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
        Schema::create('siri_sx_pt_situations', function (Blueprint $table) {
            $table->char        ('id', 64)             ->comment("Unique situation-ID for PtSituationElement. Format: CODESPACE:SituationNumber:ID");
            $table->timestamp   ('creation_time')      ->comment('Timestamp for when the situation was created');
            $table->char        ('participant_ref', 64)->comment("Codespace of the data source");
            $table->char        ('progress', 8)        ->comment("Status of a situation message. 'open' or 'closed'");
            $table->timestamp   ('valid_start')        ->nullable()->comment("Validity period start time");
            $table->timestamp   ('valid_end')          ->nullable()->comment("Validity period end time");
            $table->char        ('severity')           ->default('normal')->comment("How severely the situation affects public transport services. Enumeration");
            $table->smallInteger('priority')           ->nullable()->comment("Number value from 1 to 10 indicating the priority (urgency) of the situation message");
            $table->char        ('report_type', 12)    ->comment("Type of situation report. 'general' or 'incident'");
            $table->boolean     ('planned')            ->nullable()->comment("Whether the situation in question is due to planned events, or an unexpected incident");
            $table->tinyText    ('summary')            ->comment("The textual summary of the situation");
            $table->text        ('description')        ->nullable()->comment("Expanded textual description of the situation");
            $table->tinyText    ('advice')             ->nullable()->comment("Textual advice on how a passenger should react/respond to the situation");

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
        Schema::dropIfExists('siri_sx_pt_situations');
    }
};
