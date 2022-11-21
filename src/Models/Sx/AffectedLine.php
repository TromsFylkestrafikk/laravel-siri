<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\AffectedLine
 *
 * @property string $id Internal ID used for eloquent model relationships
 * @property string $pt_situation_id Reference to situation in question
 * @property string $line_ref Reference to Line in question (ID to the corresponding object in NeTEx).
 * @property-read \Illuminate\Database\Eloquent\Collection|\TromsFylkestrafikk\Siri\Models\Sx\AffectedStopPoint[] $affectedStopPoints
 * @property-read int|null $affected_stop_points_count
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation $ptSituation
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedLine query()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedLine whereLineRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedLine wherePtSituationId($value)
 * @mixin \Eloquent
 */
class AffectedLine extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'siri_sx_affected_line';
    protected $keyType = 'string';
    protected $fillable = ['id', 'pt_situation_id', 'line_ref'];

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
