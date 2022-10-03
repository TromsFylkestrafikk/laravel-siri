<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffectedLine extends Model
{
    use HasFactory;

    protected $table = 'siri_sx_affected_line';
    protected $fillable = ['pt_situation_id', 'line_ref'];

    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }
}
