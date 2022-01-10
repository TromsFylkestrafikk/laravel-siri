<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Helpers\XmlFile;
use TromsFylkestrafikk\Siri\Siri;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;

class SiriClientController extends Controller
{
    /**
     * SIRI channel for our request.
     *
     * @var string
     */
    protected $channel;

    /**
     * Generated filename for incoming SIRI XML.
     *
     * @var \TromsFylkestrafikk\Siri\Helpers\XmlFile
     */
    protected $xmlFile;

    /**
     * Current subscription.
     *
     * @var \TromsFylkestrafikk\Siri\Models\SiriSubscription
     */
    protected $subscription;

    public function consume(Request $request, $channel, SiriSubscription $subscription)
    {
        $this->channel = $channel;
        $this->subscription = $subscription;
        $xmlFile = XmlFile::create($this->channel);
        $xmlFile->put($request->getContent(true));
        $reader = new ChristmasTreeParser();
        $reader->open($xmlFile->getPath());
        $reader->addCallback(['Siri', 'SubscriptionResponse'], [$this, 'subscriptionResponse'])
            ->addCallback(['Siri', 'HeartbeatNotification'], [$this, 'heartbeatNotification'])
            ->addCallback(['Siri', 'ServiceDelivery'], [$this, 'serviceDelivery'])
            ->parse()
            ->close();
        return sprintf("Got request of type %s on subscription '%s'", $channel, $subscription->id);
    }

    public function subscriptionResponse(ChristmasTreeParser $reader)
    {
        Log::debug("Got subscription response");
    }

    public function heartbeatNotification(ChristmasTreeParser $reader)
    {
        Log::debug("Got heartbeat notification response");
        $xml = $reader->expandSimpleXml()->children(Siri::NS);
        dump($xml->RequestTimestamp);
    }

    public function serviceDelivery(ChristmasTreeParser $reader)
    {
        Log::debug("Got service delivery. Actual content.");
    }
}
