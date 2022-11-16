<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\AffectedJourney
 *
 * @property int $id Internal Laravel ID used for eloquent model relationships
 * @property string $pt_situation_id Reference to situation in question
 * @property string $journey_ref Reference to affected NeTEx VehicleJourney ID
 * @property string|null $data_frame_ref Journey date, if encapsulated in FramedVehicleJourneyRef
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation|null $ptSituation
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedJourney newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedJourney newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedJourney query()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedJourney whereDataFrameRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedJourney whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedJourney whereJourneyRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedJourney wherePtSituationId($value)
 * @mixin \Eloquent
 */
class AffectedJourney extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'siri_sx_affected_journey';
    protected $fillable = ['id', 'pt_situation_id', 'journey_ref', 'data_frame_ref'];

    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }

    public function route()
    {
        return $this->hasOne(AffectedRoute::class);
    }
}
