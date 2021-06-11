<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use Illuminate\Http\Request;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;

class SiriClientController extends Controller
{
    public function consume(Request $request, $channel, SiriSubscription $subscription)
    {
        return sprintf("Got request of type %s on subscription '%s'", $channel, $subscription->id);
    }
}
