<?php

/**
 * Development controller
 *
 * Logic related to development of this package.
 */

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        return <<<EOT
<?xml version="1.0"?>
<AA:Siri xmlns:AA="http://www.siri.org.uk/siri" xmlns:AD="http://www.ifopt.org.uk/acsb" xmlns:AC="http://datex2.eu/schema/1_0/1_0" xmlns:AB="http://www.ifopt.org.uk/ifopt" xmlns:xs="http://www.w3.org/2001/XMLSchema-instance" version="1.4">
  <AA:SubscriptionResponse xs:type="AA:SubscriptionResponseStructure">
    <AA:ResponseTimestamp>2021-06-12T15:38:08.011</AA:ResponseTimestamp>
    <AA:ResponderRef>SIRI Service provider</AA:ResponderRef>
    <AA:RequestMessageRef xs:type="AA:MessageQualifierStructure">RequestorMsg</AA:RequestMessageRef>
    <AA:ResponseStatus xs:type="AA:StatusResponseStructure">
      <AA:ResponseTimestamp>2021-06-12T15:38:08.011</AA:ResponseTimestamp>
      <AA:SubscriberRef>Client company</AA:SubscriberRef>
      <AA:SubscriptionRef>0b985f97-ca46-4467-994a-4c908f346655</AA:SubscriptionRef>
      <AA:Status>true</AA:Status>
      <AA:ShortestPossibleCycle>PT1S</AA:ShortestPossibleCycle>
    </AA:ResponseStatus>
    <AA:ServiceStartedTime>2021-06-12T15:38:08.011</AA:ServiceStartedTime>
  </AA:SubscriptionResponse>
</AA:Siri>
EOT;
    }

    /**
     * Emulate subscription response with failure.
     */
    public function subscribeFailed()
    {
        return response("You're not allowed here", Response::HTTP_FORBIDDEN);
    }
}
