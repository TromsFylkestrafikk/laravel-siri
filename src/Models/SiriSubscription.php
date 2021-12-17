<?php

namespace TromsFylkestrafikk\Siri\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $channel
 * @property string $subscription_url
 * @property string $requestor_ref
 * @property string $heartbeat_interval
 * @property int $active
 * @property int $received
 * @property \datetime $created_at
 * @property \datetime $updated_at
 * @property-read string $is_active
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription whereHeartbeatInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription whereReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription whereRequestorRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription whereSubscriptionUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SiriSubscription whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
