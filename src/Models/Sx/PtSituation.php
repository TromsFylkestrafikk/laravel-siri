<?php

namespace TromsFylkestrafikk\Siri\Models\Sx;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtSituation extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $table = 'siri_sx_pt_situation';
    protected $keyType = 'string';
}
