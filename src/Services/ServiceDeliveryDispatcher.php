<?php

namespace TromsFylkestrafikk\Siri\Services;

use TromsFylkestrafikk\Siri\Siri;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Helpers\XmlFile;

class ServiceDeliveryDispatcher
{
    /**
     * @var XmlFile
     */
    public $xmlFile;

    /**
     * @var SiriSubscription
     */
    protected $subscription;

    /**
     * @param SiriSubscription $subscription
     * @param XmlFile $xmlFile
     */
    public function __construct(SiriSubscription $subscription, XmlFile $xmlFile)
    {
        $this->subscription = $subscription;
        $this->xmlFile = $xmlFile;
    }

    public function dispatch()
    {
        $handlerClass = sprintf("\\TromsFylkestrafikk\\Siri\\ServiceDelivery\\%s", Siri::$serviceMap[$this->subscription->channel]);
        /** @var \TromsFylkestrafikk\Siri\ServiceDelivery\Base $handler */
        $handler = new $handlerClass($this->subscription, $this->xmlFile);
        $handler->process();
        $this->maybeDeleteXml();
    }

    /**
     * Clean up XML file after processing.
     *
     * @return bool
     */
    public function maybeDeleteXml()
    {
        if (config('siri.save_xml.' . $this->subscription->channel, false)) {
            return true;
        }
        return $this->xmlFile->delete();
    }
}
