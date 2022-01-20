<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Services\XmlMapper;
use TromsFylkestrafikk\Siri\Events\VmActivities;
use TromsFylkestrafikk\Siri\Events\VmActivity;

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
     * @var int
     */
    protected $activityCount;

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
        $start = microtime(true);
        parent::process();
        $this->emitActivities();
        $this->logDebug(
            "Parsed %d vehicle activities in %.3f seconds",
            count($this->activities),
            microtime(true) - $start
        );
    }

    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['VehicleMonitoringDelivery'], [$this, 'vmDelivery'])
            ->addNestedCallback(['VehicleMonitoringDelivery', 'SubscriberRef'], [$this, 'readSubscriberRef'])
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
        $this->activityCount = 0;
        $this->chunkCount = 0;
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function vehicleActivity()
    {
        $this->assertAuthenticated();
        $xml = $this->reader->expandSimpleXml();
        $mapper = new XmlMapper($xml, static::$activitySchema);
        $activity = $mapper->execute();
        $this->chunkCount++;
        $this->activities[] = $activity;
        $this->emitActivity($activity);
        $this->maybeEmitActivities();
    }

    protected function emitActivity($activity)
    {
        VmActivity::dispatch($this->subscription->id, $activity, $this->subscriberRef, $this->producerRef);
    }

    protected function maybeEmitActivities()
    {
        if ($this->maxChunkSize && $this->chunkCount >= $this->maxChunkSize) {
            $this->emitActivities();
            $this->activities = [];
            $this->chunkCount = 0;
        }
    }

    protected function emitActivities()
    {
        $this->logDebug("Emitting all activities (%d)", $this->chunkCount);
        VmActivities::dispatch($this->subscription->id, $this->activities, $this->subscriberRef, $this->producerRef);
    }
}
