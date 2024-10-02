<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * TromsFylkestrafikk\Siri\Models\Sx\InfoLink
 *
 * @property int $id Internal ID used for eloquent model relationships
 * @property string $pt_situation_id Reference to situation in question
 * @property string $line_ref Reference to Line in question (ID to the corresponding object in NeTEx).
 * @property-read \TromsFylkestrafikk\Siri\Models\Sx\PtSituation $ptSituation
 * @method static \Illuminate\Database\Eloquent\Builder|InfoLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InfoLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InfoLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|InfoLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InfoLink whereLineRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InfoLink wherePtSituationId($value)
 * @mixin \Eloquent
 */
class InfoLink extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'siri_sx_info_link';
    protected $fillable = ['pt_situation_id', 'uri', 'label'];

    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }
}
