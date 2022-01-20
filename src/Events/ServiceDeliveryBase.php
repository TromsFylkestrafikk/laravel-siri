<?php

namespace TromsFylkestrafikk\Siri\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceDeliveryBase
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var int
     */
    public $subscriptionId;

    /**
     * @var string
     */
    public $subscriberRef;

    /**
     * @var string
     */
    public $producerRef;

    /**
     * @var array
     */
    public $journeys;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($subId, $subscriberRef = null, $producerRef = null)
    {
        $this->subscriptionId = $subId;
        $this->subscriberRef = $subscriberRef;
        $this->producerRef = $producerRef;
    }
}
