<?php

namespace TromsFylkestrafikk\Siri\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EtDelivery
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
    public function __construct($subId, $journeys, $subRef = null, $prodRef = null)
    {
        $this->subscriptionId = $subId;
        $this->journeys = $journeys;
        $this->subscriberRef = $subRef;
        $this->producerRef = $prodRef;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('siri.et');
    }
}
