<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiriSubscription extends Model
{
    use HasFactory;

    protected $table = 'siri_subscriptions';
    protected $guarded = ['id'];
}
