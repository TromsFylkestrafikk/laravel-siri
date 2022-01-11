<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TromsFylkestrafikk\Siri\Exceptions\IllegalStateException;
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

    /**
     * @var bool
     */
    protected $validXml;

    /**
     * @var string
     */
    protected $xmlType;

    /**
     * Use queued handling of SIRI XML consumtion.
     *
     * @var bool
     */
    protected $queued = false;

    public function consume(Request $request, $channel, SiriSubscription $subscription)
    {
        $this->setLogPrefix('Siri[%s]: ', $channel);
        $this->channel = $channel;
        $this->subscription = $subscription;
        $xmlFile = XmlFile::create($this->channel);
        $xmlFile->put($request->getContent(true));
        $this->logDebug(
            "XML file size is %d, Configured size ('%s') is %d",
            filesize($xmlFile->getPath()),
            "siri.queue_pivot.{$this->channel}",
            config("siri.queue_pivot.{$this->channel}")
        );
        $this->queued = filesize($xmlFile->getPath()) > config("siri.queue_pivot.{$this->channel}");
        $this->validXml = false;
        try {
            $this->handleXml($xmlFile);
        } catch (IllegalStateException $e) {
            return response('Illegal XML.', Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return response('Fuzz while parsing XML');
        }
        return response($this->validXml ? 'OK' : "Got invalid XML");
    }

    protected function handleXml(XmlFile $xmlFile)
    {
        $reader = new ChristmasTreeParser();
        $reader->open($xmlFile->getPath());
        if ($this->queued) {
            // If using queued parsing, we'll just peek into the xml.
            $reader->setElementLimit(50);
        }
        $reader->addCallback(['Siri', 'SubscriptionResponse'], [$this, 'subscriptionResponse'])
            ->addCallback(['Siri', 'HeartbeatNotification'], [$this, 'heartbeatNotification'])
            ->addCallback(['Siri', 'ServiceDelivery'], [$this, 'serviceDelivery'])
            ->parse()
            ->close();
        $this->logDebug("Got XML of type %s", $this->xmlType);

        if ($this->queued) {
            $this->logDebug("Using queued processing");
            return;
        }
    }

    public function subscriptionResponse(ChristmasTreeParser $reader)
    {
        $this->logDebug("Subscription response");
        $this->xmlType = 'SubscriptionResponse';
        $this->validXml = true;
        $reader->halt();
    }

    public function heartbeatNotification(ChristmasTreeParser $reader)
    {
        $xml = $reader->expandSimpleXml()->children(Siri::NS);
        $date = new DateTime($xml->RequestTimestamp);
        $this->logDebug("Heartbeat notification date: %s", $date->format('Y-m-d H:i:s'));
        $this->subscription->received++;
        $this->subscription->save();
        $this->xmlType = 'HeartbeatNotification';
        $this->validXml = true;
        $reader->halt();
    }

    public function serviceDelivery(ChristmasTreeParser $reader)
    {
        $this->logDebug("Service delivery. Actual content.");
        $this->xmlType = 'ServiceDelivery';
        $this->validXml = true;
        if ($this->queued) {
            $reader->halt();
        }
    }
}
