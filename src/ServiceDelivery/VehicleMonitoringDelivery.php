<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;
use TromsFylkestrafikk\Siri\Siri;
use TromsFylkestrafikk\Siri\Helpers\XmlMapper;

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
     * Tree of XML elements to harvest.
     *
     * @var array
     */
    public static $activityTree = [
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
        dump($this->activities);
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
        $actXml = $reader->expandSimpleXml();
        $this->activities[] = XmlMapper::getXmlChildElements(static::$activityTree, $actXml);
    }
}
