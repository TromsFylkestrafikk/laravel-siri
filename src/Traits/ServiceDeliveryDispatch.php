<?php

namespace TromsFylkestrafikk\Siri\Traits;

use TromsFylkestrafikk\Siri\Siri;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Helpers\XmlFile;

trait ServiceDeliveryDispatch
{
    /**
     * @var SiriSubscription
     */
    protected $subscription;

    /**
     * @var XmlFile $xmlfile
     */
    protected $xmlFile;

    /**
     * @var string
     */
    protected $channel;

    /**
     * Find the proper class for ServiceDelivery and process it.
     */
    protected function dispatchServiceDelivery()
    {
        $handlerClass = sprintf("\\TromsFylkestrafikk\\Siri\\ServiceDelivery\\%s", Siri::$serviceMap[$this->channel]);
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
    protected function maybeDeleteXml()
    {
        if (config('siri.save_xml.' . $this->channel, false)) {
            return true;
        }
        return $this->xmlFile->delete();
    }
}
