<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

class EstimatedTimetableDelivery extends Base
{
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
        'DirectionRef' => 'int',
        'DatedVehicleJourneyRef' => 'string',
        'Cancellation' => 'bool',
        'VehicleMode' => 'string',
        'OperatorRef' => 'string',
        'Monitored' => 'bool',
        'BlockRef' => 'string',
        'IsCompleteStopSequence' => 'bool',
        'EstimatedCalls' => [
            'EstimatedCall' => [
                '#multiple' => true,
                'StopPointRef' => 'string',
                'Order' => 'int',
                'Cancellation' => 'bool',
                'PredictionInaccurate' => 'bool',
                'AimedArrivalTime' => 'string',
                'ExpectedArrivalTime' => 'string',
                'ArrivalBoardingActivity' => 'string',
                'AimedDepartureTime' => 'string',
                'ExpectedDepartureTime' => 'string',
                'DepartureBoardingActivity' => 'string',
            ],
        ],
    ];


    public function process()
    {
        parent::process();
        $this->logDebug("Got updates for %d journeys and %d calls", $this->journeyCount, $this->callCount);
    }

    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['EstimatedTimetableDelivery'], [$this, 'etDelivery'])
            ->addNestedCallback(['EstimatedTimetableDelivery', 'SubscriptionRef'], [$this, 'verifySubscriptionRef'])
            ->addNestedCallback(
                ['EstimatedTimetableDelivery', 'EstimatedJourneyVersionFrame', 'EstimatedVehicleJourney'],
                [$this, 'estimatedVehicleJourney']
            )
            ->parse()
            ->close();
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
