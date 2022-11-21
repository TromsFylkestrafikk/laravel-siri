<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\AffectedStopPoint
 *
 * @property int $id Internal ID used for eloquent model relationships
 * @property string $pt_situation_id Reference to situation this stop point is part of
 * @property string|null $parent_situation_id Situation this stop point is a direct affected child of
 * @property string|null $affected_line_id Reference to affected line this stop point is child
 * @property string|null $affected_journey_id Reference to affected journey this stop point is child of
 * @property string $stop_point_ref Reference to affected stop point.
 * @property string|null $stop_condition Specifies which passengers the message applies to, for example, people who are disembarking at an affected stop
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\AffectedJourney|null $affectedJourney
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\AffectedLine|null $affectedLine
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation|null $parentSituation
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation $ptSituation
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereAffectedJourneyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereAffectedLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereParentSituationId($value)
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
    protected $fillable = [
        'id',
        'pt_situation_id',
        'parent_situation_id',
        'affected_line_id',
        'affected_journey_id',
        'stop_point_ref',
        'stop_condition',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }

    public function parentSituation()
    {
        return $this->belongsTo(PtSituation::class, 'parent_situation_ref');
    }

    public function affectedLine()
    {
        return $this->belongsTo(AffectedLine::class);
    }

    public function affectedJourney()
    {
        return $this->belongsTo(AffectedJourney::class);
    }
}
