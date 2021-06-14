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
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Print friendly version of 'active' bool attribute.
     *
     * @return string
     */
    public function getIsActiveAttribute()
    {
        return $this->active ? 'Yes' : 'No';
    }
}
