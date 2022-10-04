<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\AffectedRoute
 *
 * @property int $id Internal Laravel ID used for eloquent model relationships
 * @property string $pt_situation_id Reference to situation in question
 * @property string|null $route_ref Reference to NeTEx route ID in question.
 * @property int|null $affected_line_id Eloquent model ID reference to affected line
 * @property int|null $affected_journey_id Eloquent model ID reference to affected line
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\AffectedJourney|null $affectedJourney
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\AffectedLine|null $affectedLine
 * @property-read \Illuminate\Database\Eloquent\Collection|\TromsFylkestrafikk\Siri\Models\Sx\AffectedStopPoint[] $affectedStopPoints
 * @property-read int|null $affected_stop_points_count
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation|null $ptSituation
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute query()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute whereAffectedJourneyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute whereAffectedLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute wherePtSituationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute whereRouteRef($value)
 * @mixin \Eloquent
 */
class AffectedRoute extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'siri_sx_affected_route';
    protected $fillable = ['pt_situation_id', 'route_ref', 'affected_line_id'];

    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }

    public function affectedLine()
    {
        return $this->belongsTo(AffectedLine::class);
    }

    public function affectedJourney()
    {
        return $this->belongsTo(AffectedJourney::class);
    }

    public function affectedStopPoints()
    {
        return $this->hasMany(AffectedStopPoint::class);
    }
}
