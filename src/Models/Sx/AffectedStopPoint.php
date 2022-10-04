<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\AffectedStopPoint
 *
 * @property int $id Internal Laravel ID used for eloquent model relationships
 * @property string $pt_situation_id Reference to situation in question
 * @property string $stop_point_ref Reference to the Quay in question (ID corresponding to objects in NSR)
 * @property string|null $stop_condition Specifies which passengers the message applies to, for example, people who are disembarking at an affected stop
 * @property int|null $affected_route_id Eloquent model ID reference to affected route
 * @property int|null $affected_journey_id Eloquent model ID reference to affected journey
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\AffectedRoute|null $affectedRoute
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation|null $ptSituation
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereAffectedJourneyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereAffectedRouteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint wherePtSituationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereStopCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereStopPointRef($value)
 * @mixin \Eloquent
 */
class AffectedStopPoint extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'siri_sx_affected_stop_point';
    protected $fillable = ['pt_situation_id', 'stop_point_ref', 'affected_route_id'];

    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }

    public function affectedRoute()
    {
        return $this->belongsTo(AffectedRoute::class);
    }
}
