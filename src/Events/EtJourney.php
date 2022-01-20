<?php

namespace TromsFylkestrafikk\Siri\Events;

/**
 * Emits a single EstimatedVehicleJourney-ish element.
 */
class EtJourney extends ServiceDeliveryBase
{
    /**
     * @var array
     */
    public $journey;

    /**
     * Create a new ET journey event
     *
     * @param int $subscriptionId Subscription (Model) ID
     * @param array $journey Updated timetable for a single vehicle journey
     * @param string $subscriberRef Subscription reference
     * @param string $producerRef Producer reference
     */
    public function __construct($subscriptionId, $journey, $subscriberRef = null, $producerRef = null)
    {
        parent::__construct($subscriptionId, $subscriberRef, $producerRef);
        $this->journey = $journey;
    }
}
