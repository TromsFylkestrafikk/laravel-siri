<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffectedJourney extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'siri_sx_affected_journey';
    protected $fillable = [
        'pt_situation_id',
        'journey_ref',
        'data_frame_ref',
    ];

    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }
}
