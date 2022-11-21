<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\AffectedStopPoint
 *
 * @property string $id Internal ID used for eloquent model relationships
 * @property string $pt_situation_id Reference to situation this stop point is part of
 * @property string $stop_point_ref Reference to affected stop point.
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\AffectedJourney|null $affectedJourney
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\AffectedLine|null $affectedLine
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation|null $parentSituation
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation $ptSituation
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint wherePtSituationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedStopPoint whereStopPointRef($value)
 * @mixin \Eloquent
 */
class AffectedStopPoint extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'id',
        'pt_situation_id',
        'stop_point_ref',
    ];
    protected $keyType = 'string';
    protected $table = 'siri_sx_affected_stop_point';

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
    public function ptSituations()
    {
        return $this->morphedByMany(PtSituation::class, 'stoppable', 'siri_sx_stoppable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function affectedJourneys()
    {
        return $this->morphedByMany(AffectedJourney::class, 'stoppable', 'siri_sx_stoppable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function affectedLines()
    {
        return $this->morphedByMany(AffectedLine::class, 'stoppable', 'siri_sx_stoppable');
    }
}
