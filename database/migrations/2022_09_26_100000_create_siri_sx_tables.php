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
        Schema::create('siri_sx_pt_situation', function (Blueprint $table) {
            $table->char        ('id', 64)             ->primary()->comment("Unique situation-ID for PtSituationElement. Format: CODESPACE:SituationNumber:ID");
            $table->timestamp   ('creation_time')      ->comment('Timestamp for when the situation was created');
            $table->char        ('participant_ref', 64)->comment("Codespace of the data source");
            $table->char        ('source_type', 16)    ->nullable()->comment("Information type: Possible values: 'directReport'");
            $table->string      ('source_name', 128)   ->nullable()->comment("Who or what is the source of the situation");
            $table->char        ('progress', 8)        ->comment("Status of a situation message. 'open' or 'closed'");
            $table->timestamp   ('validity_start')     ->nullable()->comment("Validity period start time");
            $table->timestamp   ('validity_end')       ->nullable()->comment("Validity period end time");
            $table->char        ('severity')           ->default('normal')->comment("How severely the situation affects public transport services. Enumeration");
            $table->smallInteger('priority')           ->nullable()->comment("Number value from 1 to 10 indicating the priority (urgency) of the situation message");
            $table->char        ('report_type', 12)    ->comment("Type of situation report. 'general' or 'incident'");
            $table->boolean     ('planned')            ->nullable()->comment("Whether the situation in question is due to planned events, or an unexpected incident");
            $table->tinyText    ('summary')            ->comment("The textual summary of the situation");
            $table->text        ('description')        ->nullable()->comment("Expanded textual description of the situation");
            $table->tinyText    ('advice')             ->nullable()->comment("Textual advice on how a passenger should react/respond to the situation");

            $table->timestamps();
        });

        Schema::create('siri_sx_info_link', function (Blueprint $table) {
            $table->id('id')->comment("Internal Laravel ID used for eloquent model relationships");
            $table->char('pt_situation_id', 64)->index()->comment("Reference to situation in question");
            $table->string('url', 256)->comment("Link to a website which has further information on the situation");
            $table->string('label', 256)->comment("Label for the link.");
        });

        Schema::create('siri_sx_affected_line', function (Blueprint $table) {
            $table->char('id', 64)->primary()->comment("Internal Laravel ID used for eloquent model relationships");
            $table->char('pt_situation_id', 64)->index()->comment("Reference to situation in question");
            $table->char('line_ref', 64)->index()->comment("Reference to Line in question (ID to the corresponding object in NeTEx).");
        });

        Schema::create('siri_sx_affected_route', function (Blueprint $table) {
            $table->char('id', 64)->primary()->comment("Internal Laravel ID used for eloquent model relationships");
            $table->char('pt_situation_id', 64)->comment("Reference to situation in question");
            $table->char('route_ref', 64)->index()->comment("Reference to NeTEx route ID in question.");
        });

        Schema::create('siri_sx_affected_journey', function (Blueprint $table) {
            $table->char('id', 64)->primary()->comment("Internal Laravel ID used for eloquent model relationships");
            $table->char('pt_situation_id', 64)->comment("Reference to situation in question");
            $table->char('journey_ref', 64)->index()->comment("Reference to affected NeTEx VehicleJourney ID");
            $table->date('data_frame_ref')->nullable()->comment("Journey date, if encapsulated in FramedVehicleJourneyRef");
        });

        Schema::create('siri_sx_affected_stop_point', function (Blueprint $table) {
            $table->char('stop_point_id', 64)->index()->comment("Reference to affected stop point.");
            $table->char('pt_situation_id', 64)->comment("Reference to situation in question");
            $table->enum('StopCondition', [
                'exceptionalStop',
                'destination',
                'notStopping',
                'requestStop',
                'startPoint',
                'stop',
            ])->nullable()->comment("Specifies which passengers the message applies to, for example, people who are disembarking at an affected stop");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('siri_sx_pt_situation');
        Schema::dropIfExists('siri_sx_info_link');
        Schema::dropIfExists('siri_sx_affected_line');
        Schema::dropIfExists('siri_sx_affected_route');
        Schema::dropIfExists('siri_sx_affected_journey');
        Schema::dropIfExists('siri_sx_affected_stop_point');
    }
};
