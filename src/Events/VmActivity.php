<?php

namespace TromsFylkestrafikk\Siri\Events;

/**
 * Emits chunks of VehicleActivity-ish elements.
 */
class VmActivity extends ServiceDeliveryBase
{
    /**
     * @var array
     */
    public $activity;

    /**
     * Create a new VM vehicle activity event
     *
     * @param int $subscriptionId Subscription (Model) ID
     * @param array $activity A single vechile activity update
     * @param string $subscriberRef Subscription reference
     * @param string $producerRef Producer reference
     */
    public function __construct($subscriptionId, $activity, $subscriberRef = null, $producerRef = null)
    {
        parent::__construct($subscriptionId, $subscriberRef, $producerRef);
        $this->activity = $activity;
    }
}
