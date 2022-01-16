<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Helpers\XmlFile;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Traits\LogPrefix;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;

abstract class Base
{
    use LogPrefix;

    /**
     * @var SiriSubscription
     */
    public $subscription;

    /**
     * @var string
     */
    public $producerRef;

    /**
     * True if ServiceDelivery XML matches current subscription.
     *
     * @var bool
     */
    protected $subscriptionVerified;

    /**
     * @var XmlFile
     */
    protected $xmlFile;

    /**
     * @var ChristmasTreeParser
     */
    protected $reader;

    /**
     * Throw exception if subscription identifier doesn't match.
     *
     * @var bool
     */
    protected $haltOnSubscription;

    /**
     * @param XmlFile $xmlFile The incoming Siri XML file to process.
     */
    public function __construct(SiriSubscription $subscription, XmlFile $xmlFile)
    {
        $this->subscription = $subscription;
        $this->xmlFile = $xmlFile;
        $this->subscriptionVerified = false;
        $this->setLogPrefix('Siri[%s]: ', $subscription->channel);
        $this->haltOnSubscription = env('APP_ENV' !== 'local') || request()->root() !== env('APP_URL');
    }

    /**
     * Process XML file.
     */
    public function process()
    {
        $this->reader = new ChristmasTreeParser();
        $this->logDebug("Incoming file: %s", $this->xmlFile->getPath());
        $this->reader->open($this->xmlFile->getPath());
        $this->reader->addCallback(['Siri', 'ServiceDelivery'], [$this, 'setupHandlers'])
            ->addCallback(['Siri', 'ServiceDelivery', 'ProducerRef'], function ($reader) {
                $this->producerRef = $reader->readString();
            })
            ->parse()
            ->close();
    }

    /**
     * ChristmasTreeParser callback for 'ServiceDelivery'.
     *
     * Channels must implement this and set up their own mapping/parsing of content.
     */
    abstract public function setupHandlers();

    /**
     * ChristmasTreeParser callback for SubscriptionRef.
     *
     * Channels use this to verify that the subscription reference in XML
     * matches the route's subscription ID.
     */
    public function verifySubscriptionRef()
    {
        $xmlRef = trim($this->reader->readString());
        $this->subscriptionVerified = $xmlRef === $this->subscription->id;
        if ($this->subscriptionVerified) {
            $this->logDebug("Subscription ref OK (%s)", $xmlRef);
            return;
        }
        $this->logWarning(
            "Subscription ref authentication FAILED. Got ref: %s, Expects: %s",
            $xmlRef,
            $this->subscription->id
        );
    }
}
