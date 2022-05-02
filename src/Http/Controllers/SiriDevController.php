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
                ->get(['id', 'channel', 'subscription_url', 'subscription_ref'])
                ->keyBy('id'),
        ];
    }

    /**
     * Emulate successful subscription request response.
     */
    public function subscribeOk($version)
    {
        // Make '1.4' => 'v14', '2.0' => 'v20'
        $versionPath = sprintf("v%s", str_replace('.', '', $version));
        $now = new DateTime();
        return view(sprintf(
            'siri::dev.response.%s.subscribe-ok',
            $versionPath
        ))->with(['timestamp' => $now->format('c')]);
    }

    /**
     * Emulate subscription response with failure.
     */
    public function subscribeFailed()
    {
        return response("You're not allowed here", Response::HTTP_FORBIDDEN);
    }
}
