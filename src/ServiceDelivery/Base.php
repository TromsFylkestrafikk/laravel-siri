<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use Illuminate\Pipeline\Pipeline;
use TromsFylkestrafikk\Siri\Exceptions\IllegalStateException;
use TromsFylkestrafikk\Siri\Helpers\XmlFile;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Services\XmlMapper;
use TromsFylkestrafikk\Siri\Siri;
use TromsFylkestrafikk\Siri\Traits\LogPrefix;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;

abstract class Base
{
    use LogPrefix;

    /**
     * @var \TromsFylkestrafikk\Siri\Models\SiriSubscription
     */
    protected $subscription;

    /**
     * @var \TromsFylkestrafikk\Siri\Helpers\XmlFile
     */
    protected $xmlFile;

    /**
     * @var \TromsFylkestrafikk\Xml\ChristmasTreeParser
     */
    protected $reader;

    /**
     * Schema used to map XML elements to target array.
     *
     * @var array
     */
    protected $schemas;

    /**
     * @var string
     */
    protected $responseTimestamp;

    /**
     * @var string
     */
    protected $subscriberRef;

    /**
     * @var string
     */
    protected $producerRef;

    /**
     * True if ServiceDelivery XML matches current subscription.
     *
     * @var bool
     */
    protected $subscriptionVerified;

    /**
     * Throw exception if subscription identifier doesn't match.
     *
     * @var bool
     */
    protected $haltOnSubscription;

    /**
     * Elements read per channel before emitting event.
     *
     * @var int
     */
    protected $chunkCount;

    /**
     * Total number of channel specific elements read
     *
     * @var int
     */
    protected $elementCount;

    /**
     * @var int
     */
    protected $maxChunkSize;

    /**
     * Data sent to channel event for channel.
     *
     * @var array
     */
    protected $payload;

    /**
     * @param \TromsFylkestrafikk\Siri\Helpers\XmlFile $xmlFile The incoming Siri XML file to process.
     */
    public function __construct(SiriSubscription $subscription, XmlFile $xmlFile)
    {
        $this->subscription = $subscription;
        $this->xmlFile = $xmlFile;
        $this->subscriptionVerified = true;
        $this->setLogPrefix('Siri-%s[%d]: ', $subscription->channel, $subscription->id);
        $this->haltOnSubscription = env('APP_ENV' !== 'local') || request()->root() !== env('APP_URL');
        $this->maxChunkSize = config('siri.event_chunk_size.' . $subscription->channel);
    }

    /**
     * Process XML file.
     */
    public function process()
    {
        $start = microtime(true);
        $chanElement = Siri::$serviceMap[$this->subscription->channel];
        $this->reader = new ChristmasTreeParser();

        $this->reader->open($this->xmlFile->getPath());
        $this->reader
            ->addCallback(['Siri', 'ServiceDelivery', 'ProducerRef'], function (ChristmasTreeParser $reader) {
                $this->producerRef = trim($reader->readString());
            })
            ->addCallback(['Siri', 'ServiceDelivery', $chanElement], function () {
                $this->chunkCount = 0;
                $this->elementCount = 0;
                $this->payload = [];
            })
            ->withParents(['Siri', 'ServiceDelivery', $chanElement], function (ChristmasTreeParser $reader) {
                $reader->addCallback(['ResponseTimestamp'], [$this, 'readResponseTimestamp'])
                    ->addCallback(['SubscriberRef'], [$this, 'readSubscriberRef'])
                    ->addCallback(['SubscriptionRef'], [$this, 'verifySubscriptionRef']);
                $this->setupHandlers();
            })
            ->parse()
            ->close();
        $this->emitPayload();
        $this->logInfo("Parsed %d items in %.3f seconds", $this->elementCount, microtime(true) - $start);
    }

    /**
     * Get target schema of current channel.
     *
     * @param string $elName The element in question we need to get a schema for.
     *
     * @return array
     */
    abstract protected function getTargetSchema($elName);

    /**
     * Emit current payload
     */
    abstract protected function emitPayload();

    /**
     * Setup callback handlers under 'Siri/ServiceDelivery/[<CHANNEL>]'.
     *
     * Channels must implement this and set up their own mapping/parsing of
     * content.
     */
    abstract public function setupHandlers();

    /**
     * ChristmasTreeParser callback for setting responseTimestamp
     *
     * All service deliveries may have this within their channel specific root
     * element.
     */
    public function readResponseTimestamp()
    {
        $this->responseTimestamp = trim($this->reader->readString());
    }

    /**
     * ChristmasTreeParser callback for setting subscriberRef
     *
     * All service deliveries has this within their channel specific root
     * element.
     */
    public function readSubscriberRef()
    {
        $this->subscriberRef = trim($this->reader->readString());
    }

    /**
     * ChristmasTreeParser callback for SubscriptionRef.
     *
     * Channels use this to verify that the subscription reference in XML
     * matches the route's subscription ID.
     */
    public function verifySubscriptionRef()
    {
        $xmlRef = trim($this->reader->readString());
        $this->subscriptionVerified = $xmlRef === $this->subscription->subscription_ref;
        if ($this->subscriptionVerified) {
            $this->logDebug("Subscription ref OK (%s)", $xmlRef);
            return;
        }
        $this->logWarning(
            "Subscription authentication FAILED. Got ref: %s, Expects: %s",
            $xmlRef,
            $this->subscription->subscription_ref
        );
    }

    /**
     * Assert subscription authentication is in order.
     *
     * Call this from the XML tree where it is expected that the subscription
     * ref is received, i.e. when the 'meat' of the transmission begins.
     */
    protected function assertAuthenticated()
    {
        if (!$this->subscriptionVerified && $this->haltOnSubscription) {
            $this->logError("Subscription identifier in XML is missing or doesn't match. Halting!");
            $this->reader->halt();
            throw new IllegalStateException("Wrong Subscription identifier");
        }
    }

    /**
     * Parse and add element to payload.
     *
     * Helper function for channel implementations.  Call this during parsing of
     * what is considered the 'main' payload element in the channel's service
     * delivery.
     *
     * @return array The populated target array after processing.
     */
    protected function processChannelPayloadElement()
    {
        $this->assertAuthenticated();
        $elName = $this->reader->elementName;
        $xml = $this->reader->expandSimpleXml();
        $mapper = new XmlMapper($xml, $this->getFinalTargetSchema($elName));
        $result = $mapper->execute();
        $this->chunkCount++;
        $this->elementCount++;
        $this->payload[] = $result;
        $this->maybeEmitPayload();
        return $result;
    }

    /**
     * Retrieve schema from channel and allow other to modify it.
     *
     * @param string $elName
     *
     * @return array Actual schema that will be used to map content.
     */
    protected function getFinalTargetSchema($elName)
    {
        $schemaKey = $elName ?: 'null';
        if (empty($this->schemas[$schemaKey])) {
            $schema = app(Pipeline::class)
                ->send([
                    'schema' => $this->getTargetSchema($elName),
                    'elementName' => $elName,
                    'channel' => $this->subscription->channel,
                    'siriVersion' => $this->subscription->version,
                    'subscriptionId' => $this->subscription->id,
                    'subscriptionName' => $this->subscription->name,
                ])
                ->through(config('siri.schema_pipeline', []))
                ->then(function ($processed) {
                    return $processed['schema'];
                });
            $this->schemas[$schemaKey] = $schema;
        }
        return $this->schemas[$schemaKey];
    }

    protected function createPayload(string $key, $content)
    {
        /** @var \TromsFylkestrafikk\Siri\Services\CaseStyler */
        $case = app('siri.case');
        return [
            'version' => $this->subscription->version,
            $case->style('ResponseTimestamp') => $this->responseTimestamp,
            $case->style('SubscriberRef') => $this->subscriberRef,
            $case->style('ProducerRef') => $this->producerRef,
            $case->style($key) => $content,
        ];
    }

    protected function maybeEmitPayload()
    {
        if ($this->maxChunkSize && $this->chunkCount >= $this->maxChunkSize) {
            $this->emitPayload();
            $this->payload = [];
            $this->chunkCount = 0;
        }
    }
}
