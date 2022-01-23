<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Events\VmActivities;
use TromsFylkestrafikk\Siri\Events\VmActivity;

class VehicleMonitoringDelivery extends Base
{
    /**
     * @var mixed[]
     */
    protected $activities;

    /**
     * @var int
     */
    protected $activityCount;

    /**
     * @inheritdoc
     */
    public function getTargetSchema($elName)
    {
        return [
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
    }

    /**
     * @inheritdoc
     */
    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['VehicleActivity'], [$this, 'vehicleActivity']);
    }

    /**
     * ChristmasTreeParser callback for VM main payload element.
     */
    public function vehicleActivity()
    {
        $activity = $this->processChannelPayloadElement();
        VmActivity::dispatch($this->subscription->id, $this->createPayload('VehicleActivity', $activity));
    }

    protected function emitPayload()
    {
        $this->logDebug("Emitting all activities (%d)", $this->chunkCount);
        VmActivities::dispatch($this->subscription->id, $this->createPayload('VehicleActivity', $this->payload));
    }
}
