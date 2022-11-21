<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\AffectedJourney
 *
 * @property string $id Internal ID used for eloquent model relationships
 * @property string $pt_situation_id Reference to situation in question
 * @property string $journey_ref Reference to affected NeTEx VehicleJourney ID
 * @property string|null $data_frame_ref Journey date, if encapsulated in FramedVehicleJourneyRef
 * @property-read \Illuminate\Database\Eloquent\Collection|\TromsFylkestrafikk\Siri\Models\Sx\AffectedStopPoint[] $affectedStopPoints
 * @property-read int|null $affected_stop_points_count
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation $ptSituation
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

    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'pt_situation_id', 'journey_ref', 'data_frame_ref'];
    protected $table = 'siri_sx_affected_journey';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function affectedStopPoints()
    {
        return $this->morphToMany(AffectedStopPoint::class, 'stoppable', 'siri_sx_stoppable');
    }
}
