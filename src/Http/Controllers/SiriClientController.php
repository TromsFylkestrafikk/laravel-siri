<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\Siri\Helpers\XmlFile;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Traits\LogPrefix;
use TromsFylkestrafikk\Siri\Siri;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;

class SiriClientController extends Controller
{
    use LogPrefix;

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
        $this->setLogPrefix('Siri[%s]: ', $channel);
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
    }

    public function subscriptionResponse(ChristmasTreeParser $reader)
    {
        $this->logDebug("Subscription response");
    }

    public function heartbeatNotification(ChristmasTreeParser $reader)
    {
        $xml = $reader->expandSimpleXml()->children(Siri::NS);
        $date = new DateTime($xml->RequestTimestamp);
        $this->logDebug("Heartbeat notification date: %s", $date->format('Y-m-d H:i:s'));
        $this->subscription->received++;
        $this->subscription->save();
    }

    public function serviceDelivery(ChristmasTreeParser $reader)
    {
        $this->logDebug("Service delivery. Actual content.");
    }
}
