<?php

namespace TromsFylkestrafikk\Siri\Events\EtDelivery;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EstimatedVehicleJourney
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var int
     */
    public $subscriptionId;

    /**
     * @var array
     */
    public $journey;

    /**
     * @var string
     */
    public $subscriberRef;

    /**
     * @var string
     */
    public $producerRef;

    /**
     * Create a new event instance.
     *
     * @param int $subscriptionId Subscription (Model) ID
     * @param array $journey Array with populated journey data
     * @param string $subscriberRef Subscription reference
     * @param string $producerRef Producer reference
     */
    public function __construct($subscriptionId, $journey, $subscriberRef = null, $producerRef = null)
    {
        $this->subscriptionId = $subscriptionId;
        $this->journey = $journey;
        $this->subscriberRef = $subscriberRef;
        $this->producerRef = $producerRef;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('siri.et.journey');
    }
}
