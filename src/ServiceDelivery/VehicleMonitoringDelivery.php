<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Xml\ChristmasTreeParser;
use TromsFylkestrafikk\Siri\Services\XmlMapper;

class VehicleMonitoringDelivery extends Base
{
    /**
     * @var string
     */
    protected $subscriberRef;

    /**
     * @var mixed[]
     */
    protected $activities;

    /**
     * Tree of XML elements to harvest.
     *
     * @var array
     */
    public static $activitySchema = [
        'RecordedAtTime' => 'string',
        'ProgressBetweenStops' => [
            'LinkDistance' => 'float',
            'Percentage' => 'float',
        ],
        'MonitoredVehicleJourney' => [
            'LineRef' => 'string',
            'FramedVehicleJourneyRef' => [
                'DataFrameRef' => 'string',
                'DatedVehicleJourneyRef' => 'string',
            ],
            'PublishedLineName' => 'string',
            'Monitored' => 'bool',
            'VehicleLocation' => [
                'Latitude' => 'float',
                'Longitude' => 'float',
            ],
            'Bearing' => 'string',
            'Delay' => 'string',
            'VehicleRef' => 'string',
            'PreviousCalls' => [
                'PreviousCall' => [
                    '#multiple' => true,
                    'StopPointRef' => 'string',
                    'VisitNumber' => 'string',
                    'StopPointName' => 'string',
                    'VehicleAtStop' => 'bool',
                ],
            ],
            'MonitoredCall' => [
                'StopPointRef' => 'string',
                'VisitNumber' => 'string',
                'StopPointName' => 'string',
                'VehicleAtStop' => 'bool',
            ],
        ],
    ];

    public function process()
    {
        parent::process();
        $this->logDebug(
            "Parsed %d vehicle activities in %.3f seconds",
            count($this->activities),
            microtime(true) - LARAVEL_START
        );
    }

    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['VehicleMonitoringDelivery'], [$this, 'vmDelivery'])
            ->addNestedCallback(['VehicleMonitoringDelivery', 'SubscriberRef'], function (ChristmasTreeParser $reader) {
                $this->subscriberRef = trim($reader->readString());
            })
            ->addNestedCallback(['VehicleMonitoringDelivery', 'SubscriptionRef'], [$this, 'verifySubscriptionRef'])
            ->addNestedCallback(['VehicleMonitoringDelivery', 'VehicleActivity'], [$this, 'vehicleActivity']);
    }

    /**
     * ChristmasTreeParser callback.
     *
     * Prepare target for VehicleMonitoringDelivery content.
     */
    public function vmDelivery()
    {
        $this->activities = [];
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function vehicleActivity()
    {
        $this->assertAuthenticated();
        $xml = $this->reader->expandSimpleXml();
        $mapper = new XmlMapper($xml, static::$activitySchema);
        $this->activities[] = $mapper->execute();
    }
}
