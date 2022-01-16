<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

class EstimatedTimetableDelivery extends Base
{
    /**
     * @var mixed[]
     */
    protected $journeys;

    /**
     * @var int
     */
    protected $callCount;

    /**
     * @var int
     */
    protected $journeyCount;

    /**
     * @var array
     */
    protected static $journeySchema = [
        'LineRef' => 'string',
        'DirectionRef' => 'string',
        'DatedVehicleJourneyRef' => 'string',
        'Cancellation' => 'bool',
        'PublishedLineName' => 'string',
        'ProductCategoryRef' => 'string',
        'ServiceFeatureRef' => 'string',
        'VehicleFeatureRef' => 'string',
        'VehicleJourneyName' => 'string',
        'VehicleMode' => 'string',
        'JourneyNote' => 'string',
        'OperatorRef' => 'string',
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


    public function process()
    {
        parent::process();
        $this->logDebug("Got timetables %d journeys and %d calls", $this->journeyCount, $this->callCount);
    }

    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['EstimatedTimetableDelivery'], [$this, 'etDelivery'])
            ->addNestedCallback(['EstimatedTimetableDelivery', 'SubscriptionRef'], [$this, 'verifySubscriptionRef'])
            ->addNestedCallback(
                ['EstimatedTimetableDelivery', 'EstimatedJourneyVersionFrame', 'EstimatedVehicleJourney'],
                [$this, 'estimatedVehicleJourney']
            );
    }

    public function etDelivery()
    {
        $this->journeys = [];
        $this->journeyCount = 0;
        $this->callCount = 0;
        $this->logDebug("EstimatedTimetable delivery");
    }

    public function estimatedVehicleJourney()
    {
        $xml = $this->reader->expandSimpleXml();
        $etJourney = app('siri.xml_mapper')->getXmlElements(static::$journeySchema, $xml);
        $this->journeyCount++;
        $this->callCount += empty($etJourney['estimated_calls']['estimated_call'])
            ? 0
            : count($etJourney['estimated_calls']['estimated_call']);
        $this->journeys[] = $etJourney;
    }
}
