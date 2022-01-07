<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;

class SiriClientController extends Controller
{
    public function consume($channel, SiriSubscription $subscription)
    {
        $reader = new ChristmasTreeParser();
        $reader->addCallback(['Siri', 'SubscriptionResponse'], [$this, ]);
        return sprintf("Got request of type %s on subscription '%s'", $channel, $subscription->id);
    }

    public function subscriptionResponse(ChristmasTreeParser $reader)
    {
    }
}
