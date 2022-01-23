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
            'ItemIdentifier' => 'string',
            'ValidUntilTime' => 'string',
            'VehicleMonitoringRef' => 'string',
            'ProgressBetweenStops' => [
                'LinkDistance' => 'float',
                'Percentage' => 'float',
            ],
            'MonitoredVehicleJourney' => [
                'LineRef' => 'string',
                'DirectionRef' => 'string',
                'FramedVehicleJourneyRef' => [
                    'DataFrameRef' => 'string',
                    'DatedVehicleJourneyRef' => 'string',
                ],
                'PublishedLineName' => 'string',
                'OperatorRef' => 'string',
                'ProductCategoryRef' => 'string',
                'ServiceFeatureRef' => 'string',
                'OriginName' => 'string',
                'Via' => [
                    '#multiple' => true,
                    'PlaceName' => 'string',
                ],
                'DestinationRef' => 'string',
                'DestinationName' => 'string',
                'JourneyNote' => 'string',
                'Monitored' => 'bool',
                'InCongestion' => 'bool',
                'VehicleLocation' => [
                    'Latitude' => 'float',
                    'Longitude' => 'float',
                ],
                'Bearing' => 'string',
                'ProgressRate' => 'string',
                'Delay' => 'string',
                'ProgressStatus' => 'string',
                'TrainBlockPart' => [
                    'NumberOfBlockParts' => 'int',
                    'TrainPartRef' => 'string',
                    'PositionOfTrainBlockPart' => 'int',
                ],
                'BlockRef' => 'string',
                'CourseOfJourneyRef' => 'string',
                'VehicleRef' => 'string',
                'PreviousCalls' => [
                    'PreviousCall' => [
                        '#multiple' => true,
                        'StopPointRef' => 'string',
                        'VisitNumber' => 'string',
                        'StopPointName' => 'string',
                        'VehicleAtStop' => 'bool',
                        'AimedArrivalTime' => 'string',
                        'ActualArrivalTime' => 'string',
                        'AimedDepartureTime' => 'string',
                        'ActualDepartureTime' => 'string',
                    ],
                ],
                'OnwardCalls' => [
                    'OnwardCall' => [
                        '#multiple' => true,
                        'StopPointRef' => 'string',
                        'VisitNumber' => 'string',
                        'StopPointName' => 'string',
                        'VehicleAtStop' => 'bool',
                        'AimedDepartureTime' => 'string',
                        'ExpectedDepartureTime' => 'string',
                        'AimedArrivalTime' => 'string',
                        'ExpectedArrivalTime' => 'string',
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
