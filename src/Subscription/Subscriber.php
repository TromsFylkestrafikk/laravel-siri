<?php

namespace TromsFylkestrafikk\Siri\Subscription;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;

/**
 * Perform SIRI subscription requests.
 */
class Subscriber
{
    /**
     * Send SIRI subscription request to service.
     *
     * @param \TromsFylkestrafikk\Siri\Models\SiriSubscription $subscription
     * @return bool
     */
    public static function subscribe(SiriSubscription $subscription)
    {
        $requestClass = "\\TromsFylkestrafikk\\Siri\\Subscription\\" . Str::of($subscription->channel)->lower()->studly() .  "Request";
        // @var RequestBase $request;
        $request = new $requestClass($subscription);
        return $request->sendRequest() === Response::HTTP_OK;
    }
}
