<?php

namespace TromsFylkestrafikk\Siri\Events;

/**
 * Emits a single RoadSituationElement-ish element.
 */
class SxRoadSituation extends ServiceDeliveryBase
{
    /**
     * @var array
     */
    public $roadSituation;

    /**
     * Create a new SX road situation event
     *
     * @param int $subscriptionId Subscription (Model) ID
     * @param array $roadSituation Siri SX RoadSituationElement-ish data
     * @param string $subscriberRef Subscription reference
     * @param string $producerRef Producer reference
     */
    public function __construct($subscriptionId, $roadSituation, $subscriberRef = null, $producerRef = null)
    {
        parent::__construct($subscriptionId, $subscriberRef, $producerRef);
        $this->roadSituation = $roadSituation;
    }
}
