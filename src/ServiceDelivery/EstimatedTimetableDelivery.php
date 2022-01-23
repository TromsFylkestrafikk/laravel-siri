<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Services\XmlMapper;
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
        return [
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
                    'ExtraCall' => 'bool',
                    'Order' => 'int',
                    'PredictionInaccurate' => 'bool',
                    'Occupancy' => 'string',
                    'BoardingStretch' => 'bool',
                    'RequestStop' => 'bool',
                    'CallNote' => 'string',
                    'Cancellation' => 'bool',
                    'AimedArrivalTime' => 'string',
                    'ExpectedArrivalTime' => 'string',
                    'ArrivalPlatformName' => 'string',
                    'ArrivalBoardingActivity' => 'string',
                    'AimedDepartureTime' => 'string',
                    'ExpectedDepartureTime' => 'string',
                    'DeparturePlatformName' => 'string',
                    'DepartureBoardingActivity' => 'string',
                ],
            ],
        ];
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
        EtJourneys::dispatch($this->subscription->id, $this->createPayload('EstimatedVehicleJourney', $this->payload));
    }
}
