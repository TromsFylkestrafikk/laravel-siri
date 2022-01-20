<?php

namespace TromsFylkestrafikk\Siri\Events;

/**
 * Emits chunks of EstimatedVehicleJourney-ish elements.
 */
class EtJourneys extends ServiceDeliveryBase
{
    /**
     * @var array
     */
    public $journeys;

    /**
     * Create a new ET event with many journey updates
     *
     * @param int $subscriptionId Subscription (Model) ID
     * @param array $journeys Updated timetables for many journeys
     * @param string $subscriberRef Subscription reference
     * @param string $producerRef Producer reference
     */
    public function __construct($subscriptionId, $journeys, $subscriberRef = null, $producerRef = null)
    {
        parent::__construct($subscriptionId, $subscriberRef, $producerRef);
        $this->journeys = $journeys;
    }
}
