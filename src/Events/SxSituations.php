<?php

namespace TromsFylkestrafikk\Siri\Events;

/**
 * Emits chunks of SX Situation-ish elements.
 */
class SxSituations extends ServiceDeliveryBase
{
    /**
     * @var array
     */
    public $ptSituations;

    /**
     * @var array
     */
    public $roadSituations;

    /**
     * Create a new ET event with many journey updates
     *
     * @param int $subscriptionId Subscription (Model) ID
     * @param array $ptSituations Array of point situations.
     * @param array $roadSituations Array of road situations.
     * @param string $subscriberRef Subscription reference
     * @param string $producerRef Producer reference
     */
    public function __construct($subscriptionId, $ptSituations, $roadSituations, $subscriberRef = null, $producerRef = null)
    {
        parent::__construct($subscriptionId, $subscriberRef, $producerRef);
        $this->ptSituations = $ptSituations;
        $this->roadSituations = $roadSituations;
    }
}
