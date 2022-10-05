<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoLink extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'siri_sx_affected_line';
    protected $fillable = ['pt_situation_id', 'url', 'label'];

    public function ptSituation()
    {
        return $this->belongsTo(PtSituation::class);
    }

    public function affectedRoutes()
    {
        return $this->hasMany(AffectedRoute::class);
    }
}
