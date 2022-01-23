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
     * @var array
     */
    public $payload;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($subscriptionId, $payload)
    {
        $this->subscriptionId = $subscriptionId;
        $this->payload = $payload;
    }
}
