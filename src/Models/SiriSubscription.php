<?php

namespace TromsFylkestrafikk\Siri\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiriSubscription extends Model
{
    use HasFactory;

    protected $table = 'siri_subscriptions';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
}
