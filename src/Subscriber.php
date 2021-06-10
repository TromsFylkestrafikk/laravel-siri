<?php

namespace TromsFylkestrafikk\Siri;

use TromsFylkestrafikk\Siri\Models\SiriSubscription;

/**
 * Perform SIRI subscriptions
 */
class Subscriber
{
    /**
     * Supported SIRI channels.
     */
    public const CHANNELS = ['ET', 'VM'];

    /**
     * Send SIRI subscription request to service.
     *
     * @param \TromsFylkestrafikk\Siri\Models\SiriSubscription $subscription
     * @return bool
     */
    public static function subscribe(SiriSubscription $subscription)
    {

    }
}
