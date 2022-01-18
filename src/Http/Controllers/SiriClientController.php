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
use TromsFylkestrafikk\Siri\Traits\ServiceDeliveryDispatch;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;

class SiriClientController extends Controller
{
    use LogPrefix;
    use ServiceDeliveryDispatch;

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
        $this->xmlFile = XmlFile::create($this->channel);
        $this->xmlFile->put($request->getContent(true));
        $this->logDebug(
            "XML file size is %d, Configured size ('%s') is %d",
            filesize($this->xmlFile->getPath()),
            "siri.queue_pivot.{$this->channel}",
            config("siri.queue_pivot.{$this->channel}")
        );
        $this->queued = filesize($this->xmlFile->getPath()) > config("siri.queue_pivot.{$this->channel}");
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
        $this->reader->open($this->xmlFile->getPath());
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
            $this->maybeDeleteXml();
            return;
        }
        if ($this->queued) {
            $this->logDebug("Using queued processing");
            return $this->handleQueued();
        }
        $this->dispatchServiceDelivery();
    }

    /**
     * Handle queued processing of incoming Siri Xml.
     */
    protected function handleQueued()
    {
        $this->logDebug("Sending processing of XML to background queue.");
        BackgroundXmlHandler::dispatch($this->subscription->id, $this->xmlFile, $this->channel);
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
        $this->subscription->received++;
        $this->subscription->save();
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
