<?php

/**
 * Development controller
 *
 * Logic related to development of this package.
 */

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use DateTime;
use Illuminate\Http\Response;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;

class SiriDevController extends Controller
{
    /**
     * Get at list of available, active subscribed channels.
     *
     * @return array
     */
    public function subscriptions()
    {
        return [
            'subscriptions' => SiriSubscription::whereActive(true)
                ->get(['id', 'channel', 'subscription_url'])
                ->keyBy('id'),
        ];
    }

    /**
     * Emulate successful subscription request response.
     */
    public function subscribeOk()
    {
        $now = new DateTime();
        return view('siri::dev.response.subscribe-ok')->with(['timestamp' => $now->format('c')]);
    }

    /**
     * Emulate subscription response with failure.
     */
    public function subscribeFailed()
    {
        return response("You're not allowed here", Response::HTTP_FORBIDDEN);
    }
}
