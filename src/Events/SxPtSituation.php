<?php

namespace TromsFylkestrafikk\Siri\Events;

/**
 * Emits a single PtSituationElement-ish element.
 */
class SxPtSituation extends ServiceDeliveryBase
{
    /**
     * @var array
     */
    public $ptSituation;

    /**
     * Create a new SX point situation event
     *
     * @param int $subscriptionId Subscription (Model) ID
     * @param array $ptSituation Siri SX PtSituationElement-ish data
     * @param string $subscriberRef Subscription reference
     * @param string $producerRef Producer reference
     */
    public function __construct($subscriptionId, $ptSituation, $subscriberRef = null, $producerRef = null)
    {
        parent::__construct($subscriptionId, $subscriberRef, $producerRef);
        $this->ptSituation = $ptSituation;
    }
}
