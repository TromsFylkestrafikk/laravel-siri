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
        '//siri:ProgressBetweenStops/siri:LinkDistance' => 'progressLinkDistance',
        '//siri:ProgressBetweenStops/siri:Percentage' => 'progressPercentage',
        '//siri:MonitoredVehicleJourney/siri:LineRef' => 'lineRef',
        '//siri:MonitoredVehicleJourney/siri:FramedVehicleJourneyRef/siri:DataFrameRef' => 'journeyDataFrameRef',
        '//siri:MonitoredVehicleJourney/siri:FramedVehicleJourneyRef/siri:DatedVehicleJourneyRef' => 'journeyRef',
        '//siri:MonitoredVehicleJourney/siri:PublishedLineName' => 'lineName',
        '//siri:MonitoredVehicleJourney/siri:Monitored' => 'monitored',
        '//siri:MonitoredVehicleJourney/siri:VehicleLocation/siri:Latitude' => 'latitude',
        '//siri:MonitoredVehicleJourney/siri:VehicleLocation/siri:Longitude' => 'longitude',
        '//siri:MonitoredVehicleJourney/siri:Bearing' => 'bearing',
        '//siri:MonitoredVehicleJourney/siri:Delay' => 'delay',
        '//siri:MonitoredVehicleJourney/siri:VehicleRef' => 'vehicleRef',
        '//siri:MonitoredVehicleJourney/siri:MonitoredCall/siri:StopPointRef' => 'callStopPointRef',
        '//siri:MonitoredVehicleJourney/siri:MonitoredCall/siri:VisitNumber' => 'callVisitNumber',
        '//siri:MonitoredVehicleJourney/siri:MonitoredCall/siri:StopPointName' => 'callStopPointName',
        '//siri:MonitoredVehicleJourney/siri:MonitoredCall/siri:VehicleAtStop' => 'callVehicleAtStop',
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
