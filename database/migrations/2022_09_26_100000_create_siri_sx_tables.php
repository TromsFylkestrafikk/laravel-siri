<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->timestamp   ('response_timestamp') ->default(DB::raw('now()'))->comment('Timestamp of ServiceDelivery');
            $table->char        ('participant_ref', 64)->comment("Codespace of the data source");
            $table->char        ('source_type', 16)    ->nullable()->comment("Information type: Possible values: 'directReport'");
            $table->string      ('source_name', 128)   ->nullable()->comment("Who or what is the source of the situation");
            $table->enum        ('progress', ['open', 'closed'])->comment("Status of a situation message");
            $table->timestamp   ('validity_start')     ->nullable()->comment("Validity period start time");
            $table->timestamp   ('validity_end')       ->nullable()->comment("Validity period end time");
            $table->enum        ('severity', [
                'noImpact',
                'verySlight',
                'slight',
                'normal',
                'severe',
                'verySevere',
            ])->default('normal')                      ->comment("How severely the situation affects public transport services");
            $table->smallInteger('priority')           ->nullable()->comment("Number value from 1 to 10 indicating the priority (urgency) of the situation message");
            $table->char        ('report_type', 12)    ->comment("Type of situation report. 'general' or 'incident'");
            $table->boolean     ('planned')            ->nullable()->comment("Whether the situation in question is due to planned events, or an unexpected incident");
            $table->tinyText    ('summary')            ->comment("The textual summary of the situation");
            $table->text        ('description')        ->nullable()->comment("Expanded textual description of the situation");
            $table->tinyText    ('advice')             ->nullable()->comment("Textual advice on how a passenger should react/respond to the situation");

            $table->index(['validity_start', 'validity_end'], 'siri_sx_pt_situation__validity_period');
            $table->timestamps();
        });

        Schema::create('siri_sx_info_link', function (Blueprint $table) {
            $table->id('id')->comment("Internal Laravel ID used for eloquent model relationships");
            $table->char('pt_situation_id', 64)->index()->comment("Reference to situation in question");
            $table->string('url', 256)->comment("Link to a website which has further information on the situation");
            $table->string('label', 256)->comment("Label for the link.");

            $table->foreign('pt_situation_id')->references('id')->on('siri_sx_pt_situation')->onDelete('cascade');
        });

        Schema::create('siri_sx_affected_line', function (Blueprint $table) {
            $table->char('id', 64)->primary()->comment("Internal ID used for eloquent model relationships");
            $table->char('pt_situation_id', 64)->index()->comment("Reference to situation in question");
            $table->char('line_ref', 64)->index()->comment("Reference to Line in question (ID to the corresponding object in NeTEx).");

            $table->foreign('pt_situation_id')->references('id')->on('siri_sx_pt_situation')->onDelete('cascade');
        });

        Schema::create('siri_sx_affected_route', function (Blueprint $table) {
            $table->char('id', 64)->primary()->comment("Internal ID used for eloquent model relationships");
            $table->char('pt_situation_id', 64)->comment("Reference to situation in question");
            $table->char('route_ref', 64)->index()->comment("Reference to NeTEx route ID in question.");

            $table->foreign('pt_situation_id')->references('id')->on('siri_sx_pt_situation')->onDelete('cascade');
        });

        Schema::create('siri_sx_affected_journey', function (Blueprint $table) {
            $table->char('id', 64)->primary()->comment("Internal ID used for eloquent model relationships");
            $table->char('pt_situation_id', 64)->comment("Reference to situation in question");
            $table->char('journey_ref', 64)->index()->comment("Reference to affected NeTEx VehicleJourney ID");
            $table->date('data_frame_ref')->nullable()->comment("Journey date, if encapsulated in FramedVehicleJourneyRef");

            $table->foreign('pt_situation_id')->references('id')->on('siri_sx_pt_situation')->onDelete('cascade');
        });

        Schema::create('siri_sx_affected_stop_point', function (Blueprint $table) {
            $table->char('id', 64)->primary()->comment("Internal ID used for eloquent model relationships");
            $table->char('pt_situation_id', 64)->index()->comment("Reference to situation this stop point is part of");
            $table->char('stop_point_ref', 64)->index()->comment("Reference to affected stop point.");

            $table->foreign('pt_situation_id')->references('id')->on('siri_sx_pt_situation')->onDelete('cascade');
        });

        Schema::create('siri_sx_stoppable', function (Blueprint $table) {
            $table->char('pt_situation_id', 64)->index()->comment("Reference to situation this relation is part of");
            $table->char('affected_stop_point_id', 64)->index()->comment("Reference to affected stop point in situation");
            $table->char('stoppable_type', 128)->comment("Model class name the stop relation belongs to");
            $table->char('stoppable_id', 64)->comment("Model ID the stop belongs to.");
            $table->enum('stop_condition', [
                'exceptionalStop',
                'destination',
                'notStopping',
                'requestStop',
                'startPoint',
                'stop',
            ])->nullable()->comment("Specifies which passengers the message applies to, for example, people who are disembarking at an affected stop");

            $table->index(['stoppable_type', 'stoppable_id'], 'siri_sx_stoppable__model_id');
            $table->foreign('pt_situation_id')->references('id')->on('siri_sx_pt_situation')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('siri_sx_info_link');
        Schema::dropIfExists('siri_sx_affected_line');
        Schema::dropIfExists('siri_sx_affected_route');
        Schema::dropIfExists('siri_sx_affected_journey');
        Schema::dropIfExists('siri_sx_affected_stop_point');
        Schema::dropIfExists('siri_sx_stoppable');
        Schema::dropIfExists('siri_sx_pt_situation');
    }
};
