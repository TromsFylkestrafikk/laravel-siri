<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Exceptions\IllegalStateException;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;

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
        $this->logDebug("Parsed %d vehicle activities in %.3f seconds", count($this->activities), microtime(true) - LARAVEL_START);
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
        if (!$this->subscriptionVerified && $this->haltOnSubscription) {
            $this->logError("Subscription identifier in XML is missing or doesn't match. Halting!");
            $this->reader->halt();
            throw new IllegalStateException("Wrong Subscription identifier");
        }
        $actXml = $this->reader->expandSimpleXml();
        $this->activities[] = app('siri.xml_mapper')->getXmlElements(static::$activitySchema, $actXml);
        $latest = last($this->activities);
    }
}
