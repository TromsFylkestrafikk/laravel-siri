<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;
use TromsFylkestrafikk\Siri\Siri;

class VehicleMonitoringDelivery extends Base
{
    /**
     * @var string
     */
    protected $subscriberRef;

    /**
     * @var string
     */
    protected $subscriptionId;

    /**
     * @var mixed[]
     */
    protected $activities;

    /**
     * Mapping between xpath and destination key in activity array.
     *
     * @var string[]
     */
    public static $xpathMap = [
        '//siri:RecordedAtTime' => 'recordedAtTime',
        '//siri:ProgressBetweenStops/siri:LinkDistance' => 'linkDistance',
    ];

    public function process()
    {
        parent::process();
    }

    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['VehicleMonitoringDelivery'], [$this, 'vmDelivery'])
            ->addNestedCallback(['VehicleMonitoringDelivery', 'SubscriberRef'], function (ChristmasTreeParser $reader) {
                $this->subscriberRef = trim($reader->readString());
            })
            ->addNestedCallback(['VehicleMonitoringDelivery', 'SubscriptionRef'], function (ChristmasTreeParser $reader) {
                $this->subscriptionId = trim($reader->readString());
            })
            ->addNestedCallback(['VehicleMonitoringDelivery', 'VehicleActivity'], [$this, 'vehicleActivity']);
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function vmDelivery()
    {
        Log::debug("VehicleMonitoring delivery");
        $this->activities = [];
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function vehicleActivity(ChristmasTreeParser $reader)
    {
        $activity = [];
        $actXml = $reader->expandSimpleXml();
        $actXml->registerXPathNamespace('siri', Siri::NS);
        foreach (self::$xpathMap as $xpath => $destKey) {
            $hits = $actXml->xpath($xpath);
            if (count($hits)) {
                $activity[$destKey] = trim((string) $hits[0]);
            }
        }
        $this->activities[] = $activity;
    }
}
