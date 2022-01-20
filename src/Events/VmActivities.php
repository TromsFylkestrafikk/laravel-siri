<?php

namespace TromsFylkestrafikk\Siri\Events;

/**
 * Emits chunks of VehicleActivity-ish elements.
 */
class VmActivities extends ServiceDeliveryBase
{
    /**
     * @var array
     */
    public $activities;

    /**
     * Create a new VM vehicle activities event
     *
     * @param int $subscriptionId Subscription (Model) ID
     * @param array $activities An array of vehicle activities
     * @param string $subscriberRef Subscription reference
     * @param string $producerRef Producer reference
     */
    public function __construct($subscriptionId, $activities, $subscriberRef = null, $producerRef = null)
    {
        parent::__construct($subscriptionId, $subscriberRef, $producerRef);
        $this->activities = $activities;
    }
}
