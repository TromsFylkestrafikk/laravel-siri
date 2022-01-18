<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Services\XmlMapper;

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


    /**
     * @inheritdoc
     */
    public function process()
    {
        $start = microtime(true);
        parent::process();
        $this->logDebug(
            "Processed timetables for %d journeys and %d calls in %.3f seconds.",
            $this->journeyCount,
            $this->callCount,
            microtime(true) - $start
        );
    }

    /**
     * @inheritdoc
     */
    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['EstimatedTimetableDelivery'], [$this, 'etDelivery'])
            ->addNestedCallback(['EstimatedTimetableDelivery', 'SubscriptionRef'], [$this, 'verifySubscriptionRef'])
            ->addNestedCallback(
                ['EstimatedTimetableDelivery', 'EstimatedJourneyVersionFrame', 'EstimatedVehicleJourney'],
                [$this, 'estimatedVehicleJourney']
            );
    }

    /**
     * ChristmasTreeParser callback for 'EstimatedTimetableDelivery'.
     *
     * We use this to init/reset our object.
     */
    public function etDelivery()
    {
        $this->journeys = [];
        $this->journeyCount = 0;
        $this->callCount = 0;
        $this->logDebug("EstimatedTimetable delivery");
    }

    /**
     * ChristmasTreeParser callback for EstimatedVehicleJourney.
     *
     * This is the meat of the VM xml dump.
     */
    public function estimatedVehicleJourney()
    {
        $this->assertAuthenticated();
        $xml = $this->reader->expandSimpleXml();
        $mapper = new XmlMapper($xml, static::$journeySchema);
        $etJourney = $mapper->execute();
        $this->journeyCount++;
        $this->callCount += count($mapper->get('EstimatedCalls.EstimatedCall', []));
        $this->journeys[] = $etJourney;
    }
}
