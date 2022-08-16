<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TromsFylkestrafikk\Siri\Exceptions\IllegalStateException;
use TromsFylkestrafikk\Siri\Helpers\XmlFile;
use TromsFylkestrafikk\Siri\Jobs\BackgroundXmlHandler;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Siri;
use TromsFylkestrafikk\Siri\Traits\LogPrefix;
use TromsFylkestrafikk\Siri\Services\ServiceDeliveryDispatcher;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;

class SiriClientController extends Controller
{
    use LogPrefix;

    /**
     * @var SiriSubscription
     */
    protected $subscription;

    /**
     * @var ChristmasTreeParser
     */
    protected $reader;

    /**
     * @var bool
     */
    protected $validXml;

    /**
     * @var string
     */
    protected $xmlType;

    /**
     * @var ServiceDeliveryDispatcher
     */
    protected $courier;

    /**
     * Use queued handling of SIRI XML consumtion.
     *
     * @var bool
     */
    protected $queued = false;

    public function consume(Request $request, string $channel, SiriSubscription $subscription)
    {
        $this->setLogPrefix('Siri-%s[%d]: ', $channel, $subscription->id);
        $this->subscription = $subscription;
        $xmlFile = XmlFile::create($channel);
        $xmlFile->put($request->getContent(true));
        $this->courier = new ServiceDeliveryDispatcher($subscription, $xmlFile);
        $this->queued = filesize($xmlFile->getPath()) > config("siri.queue_pivot.{$channel}");
        $this->validXml = false;
        try {
            $this->handleXml();
        } catch (IllegalStateException $e) {
            return response('Illegal XML.', Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $traceLines = array_slice(explode("\n", $e->getTraceAsString()), 0, 6);
            $trace = implode("\n", $traceLines);
            $this->logError("%s[%d]: %s\n%s", $e->getFile(), $e->getLine(), $e->getMessage(), $trace);
            return response('Fuzz while processing XML');
        }
        return response($this->validXml ? 'OK' : "Got invalid XML");
    }

    protected function handleXml()
    {
        $this->reader = new ChristmasTreeParser();
        $this->reader->open($this->courier->xmlFile->getPath());
        // We set number of read elements to something very low in this initial
        // peek phase.
        $this->reader->setElementLimit(50)
            ->addCallback(['Siri', 'SubscriptionResponse'], [$this, 'subscriptionResponse'])
            ->addCallback(['Siri', 'HeartbeatNotification'], [$this, 'heartbeatNotification'])
            ->addCallback(['Siri', 'ServiceDelivery'], [$this, 'serviceDelivery'])
            ->parse()
            ->close();
        $this->logDebug("Got XML of type %s", $this->xmlType);

        if ($this->xmlType !== 'ServiceDelivery') {
            $this->logDebug("NOT service delivery. Touching subscription");
            $this->subscription->touch();
            $this->courier->maybeDeleteXml();
            return;
        }
        $this->subscription->received++;
        $this->subscription->save();
        if ($this->queued) {
            $this->logDebug("Using queued processing");
            return $this->handleQueued();
        }
        $this->courier->dispatch();
    }

    /**
     * Handle queued processing of incoming Siri Xml.
     */
    protected function handleQueued()
    {
        $this->logDebug("Sending processing of XML to background queue.");
        BackgroundXmlHandler::dispatch($this->subscription->id, $this->courier->xmlFile->getFilename());
    }

    /**
     * ChristmasTreeParser callback handler
     */
    public function subscriptionResponse(ChristmasTreeParser $reader)
    {
        $this->xmlType = 'SubscriptionResponse';
        $this->validXml = true;
        $reader->halt();
    }

    /**
     * ChristmasTreeParser callback handler
     */
    public function heartbeatNotification(ChristmasTreeParser $reader)
    {
        $xml = $reader->expandSimpleXml()->children(Siri::NS);
        $date = new DateTime($xml->RequestTimestamp);
        $this->logDebug("Heartbeat notification date: %s", $date->format('Y-m-d H:i:s'));
        $this->xmlType = 'HeartbeatNotification';
        $this->validXml = true;
        $reader->halt();
    }

    /**
     * ChristmasTreeParser callback handler
     */
    public function serviceDelivery(ChristmasTreeParser $reader)
    {
        $this->xmlType = 'ServiceDelivery';
        $this->validXml = true;
        if ($this->queued) {
            $reader->halt();
        }
    }
}
