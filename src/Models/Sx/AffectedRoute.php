<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\AffectedRoute
 *
 * @property string $id Internal ID used for eloquent model relationships
 * @property string $pt_situation_id Reference to situation in question
 * @property string $route_ref Reference to NeTEx route ID in question.
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute query()
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute wherePtSituationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AffectedRoute whereRouteRef($value)
 * @mixin \Eloquent
 */
class AffectedRoute extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['id', 'pt_situation_id', 'route_ref', 'affected_line_id'];
    protected $keyType = 'string';
    protected $table = 'siri_sx_affected_route';
}
