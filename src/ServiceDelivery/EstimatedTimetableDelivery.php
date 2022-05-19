<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Events\EtJourney;
use TromsFylkestrafikk\Siri\Events\EtJourneys;

class EstimatedTimetableDelivery extends Base
{
    /**
     * @var int
     */
    protected $journeyCount;

    /**
     * @inheritdoc
     */
    protected function getTargetSchema($elName)
    {
        $schema = [
            'LineRef' => 'string',
            'DirectionRef' => 'string',
            'DatedVehicleJourneyRef' => 'string',
            'Cancellation' => 'bool',
            'PublishedLineName' => 'string',
            'OperatorRef' => 'string',
            'ProductCategoryRef' => 'string',
            'ServiceFeatureRef' => 'string',
            'VehicleFeatureRef' => 'string',
            'VehicleJourneyName' => 'string',
            'VehicleMode' => 'string',
            'JourneyNote' => 'string',
            'Monitored' => 'bool',
            'PredictionInaccurate' => 'bool',
            'Occupancy' => 'string',
            'BlockRef' => 'string',
            'IsCompleteStopSequence' => 'bool',
            'EstimatedCalls' => [
                'EstimatedCall' => [
                    '#multiple' => true,
                    'StopPointRef' => 'string',
                    'VisitNumber' => 'int',
                    'Order' => 'int',
                    'StopPointName' => 'string',
                    'ExtraCall' => 'bool',
                    'Cancellation' => 'bool',
                    'PredictionInaccurate' => 'bool',
                    'Occupancy' => 'string',
                    'TimingPoint' => 'bool',
                    'BoardingStretch' => 'bool',
                    'RequestStop' => 'bool',
                    'DestinationDisplay' => 'string',
                    'CallNote' => 'string',
                    'AimedArrivalTime' => 'string',
                    'ExpectedArrivalTime' => 'string',
                    'ArrivalProximityText' => 'string',
                    'ArrivalPlatformName' => 'string',
                    'ArrivalBoardingActivity' => 'string',
                    'AimedDepartureTime' => 'string',
                    'ExpectedDepartureTime' => 'string',
                    'DepartureStatus' => 'string',
                    'DepartureProximityText' => 'string',
                    'DeparturePlatformName' => 'string',
                    'DepartureBoardingActivity' => 'string',
                    'AimedHeadwayInterval' => 'string',
                    'ExpectedHeadwayInterval' => 'string',
                ],
            ],
        ];
        // Version specific elements
        if (version_compare($this->subscription->version, '2.0', '>=')) {
            $schema['FramedVehicleJourneyRef'] = [
                'DataFrameRef' => 'string',
                'DatedVehicleJourneyRef' => 'string',
            ];
            $schema['RecordedAtTime'] = 'string';
            $schema['EstimatedCalls']['EstimatedCall']['EarliestExpectedDepartureTime'] = 'string';
            $schema['EstimatedCalls']['EstimatedCall']['OriginDisplay'] = 'string';
            $schema['EstimatedCalls']['EstimatedCall']['ArrivalStatus'] = 'string';
            $schema['EstimatedCalls']['EstimatedCall']['ProvisionalExpectedDepartureTime'] = 'string';
            $schema['EstimatedCalls']['EstimatedCall']['EarliestExpectedDepartureTime'] = 'string';
            $schema['EstimatedCalls']['EstimatedCall']['AimedLatestPassengerAccessTime'] = 'string';
            $schema['EstimatedCalls']['EstimatedCall']['ExpectedLatestPassengerAccessTime'] = 'string';
            $schema['EstimatedCalls']['EstimatedCall']['DepartureStopAssignment'] = 'string';
            $schema['EstimatedCalls']['EstimatedCall']['DepartureOperatorRefs'] = 'string';
            $schema['EstimatedCalls']['EstimatedCall']['DistanceFromStop'] = 'int';
            $schema['EstimatedCalls']['EstimatedCall']['NumberOfStopsAway'] = 'int';
            $schema['RecordedCalls'] = [
                'RecordedCall' => [
                    '#multiple' => true,
                    'StopPointRef' => 'string',
                    'VisitNumber' => 'string',
                    'Order' => 'int',
                    'ExtraCall' => 'bool',
                    'Cancellation' => 'bool',
                    'PredictionInaccurate' => 'bool',
                    'Occupancy' => 'string',
                    'AimedArrivalTime' => 'string',
                    'ExpectedArrivalTime' => 'string',
                    'ActualArrivalTime' => 'string',
                    'ArrivalPlatformName' => 'string',
                    'AimedDepartureTime' => 'string',
                    'ExpectedDepartureTime' => 'string',
                    'DeparturePlatformName' => 'string',
                    'ActualDepartureTime' => 'string',
                    'AimedHeadwayInterval' => 'string',
                    'ExpectedHeadwayInterval' => 'string',
                    'ActualHeadwayInterval' => 'string',
                ],
            ];
        }
        return $schema;
    }

    /**
     * @inheritdoc
     */
    public function setupHandlers()
    {
        $this->reader->addNestedCallback(
            ['EstimatedJourneyVersionFrame', 'EstimatedVehicleJourney'],
            [$this, 'estimatedVehicleJourney']
        );
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function estimatedVehicleJourney()
    {
        $journey = $this->processChannelPayloadElement();
        EtJourney::dispatch($this->subscription->id, $this->createPayload('EstimatedVehicleJourney', $journey));
    }

    /**
     * @inheritdoc
     */
    protected function emitPayload()
    {
        $this->logDebug("Emitting all journeys (%d)", $this->chunkCount);
        EtJourneys::dispatch($this->subscription->id, $this->createPayload('EstimatedTimetableDelivery', $this->payload));
    }
}
