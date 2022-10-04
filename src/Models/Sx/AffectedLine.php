<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\AffectedLine
 *
 * @property int $id Internal Laravel ID used for eloquent model relationships
 * @property string $pt_situation_id Reference to situation in question
 * @property string $line_ref Reference to Line in question (ID to the corresponding object in NeTEx).
 * @property-read \Illuminate\Database\Eloquent\Collection|\TromsFylkestrafikk\Siri\Models\Sx\AffectedRoute[] $affectedRoutes
 * @property-read int|null $affected_routes_count
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation|null $ptSituation
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

    public $timestamps = false;
    protected $table = 'siri_sx_affected_line';
    protected $fillable = ['pt_situation_id', 'line_ref'];

    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }

    public function affectedRoutes()
    {
        return $this->hasMany(AffectedRoute::class);
    }
}
