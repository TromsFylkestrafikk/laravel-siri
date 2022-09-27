<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffectedJourney extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }
}
