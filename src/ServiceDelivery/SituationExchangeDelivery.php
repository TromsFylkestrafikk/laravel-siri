<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Services\XmlMapper;

class SituationExchangeDelivery extends Base
{
    /**
     * @var string
     */
    protected $subscriberRef;

    /**
     * @var mixed[]
     */
    protected $situations;

    /**
     * Tree of XML elements to harvest.
     *
     * @var array
     */
    public static $ptSituationSchema = [
        'CreationTime' => 'string',
        'ParticipantRef' => 'string',
        'SituationNumber' => 'string',
        'Version' => 'string',
        'References' => [
            'RelatedToRef' => [
                '#multiple' => true,
                'CreationTime' => 'string',
                'ParticipantRef' => 'string',
                'SituationNumber' => 'string',
                'RelatedAs' => 'string',
                'Version' => 'string',
            ],
        ],
        'Source' => [
            'SourceType' => 'string',
            'Name' => 'string',
            'Phone' => 'string',
            'AgentReference' => 'string',
            'TimeOfCommunication' => 'string',
        ],
        'Verification' => 'string',
        'Progress' => 'string',
        'QualityIndex' => 'string',
        'ValidityPeriod' => [
            'StartTime' => 'string',
            'EndTime' => 'string',
        ],
        'MiscellaneousReason' => 'string',
        'UndefinedReason' => 'string',
        'Severity' => 'string',
        'Audience' => 'string',
        'ReportType' => 'string',
        'Summary' => 'string',
        'Description' => 'string',
        'Affects' => [
            'Networks' => [
                'AffectedNetwork' => [
                    '#multiple',
                    'VehicleMode' => 'string',
                    'AffectedLine' => [
                        'LineRef' => 'string',
                    ],
                ],
            ],
            'StopPoints' => [
                'AffectedStopPoint' => [
                    '#multiple' => true,
                    'StopPointRef' => 'string',
                    'StopPointName' => 'string',
                    'StopPointType' => 'string',
                    'Location' => [
                        'Latitude' => 'float',
                        'Longitude' => 'float',
                        'Precision' => 'float',
                    ],
                ],
            ],
            'StopPlaces' => [
                'AffectedStopPlace' => [
                    '#multiple' => true,
                    'StopPlaceRef' => 'string',
                    'AffectedComponents' => [
                        'AffectedComponent' => [
                            '#multiple' => true,
                            'ComponentRef' => 'string',
                            'ComponentName' => 'string',
                        ],
                    ],
                ],
            ],
            'VehicleJourneys' => [
                'AffectedVehicleJourney' => [
                    '#multiple' => true,
                    'VehicleJourneyRef' => 'string',
                    'Route' => 'string',
                ],
            ],
        ],
        'Consequences' => [
            'Consequence' => [
                '#multiple' => true,
                'Period' => [
                    'StartTime' => 'string',
                    'EndTime' => 'string',
                ],
                'Condition' => 'string',
                'Severity' => 'string',
                'Blocking' => [
                    'JourneyPlanner' => 'bool',
                    'RealTime' => 'bool',
                ],
                'Boarding' => [
                    'ArrivalBoardingActivity' => 'string',
                    'DepartureBoardingActivity' => 'string',
                ],
            ],
        ],
        'PublishingActions' => [
            'PublishToWebAction' => [
                'Incidents' => 'bool',
                'HomePage' => 'bool',
                'Ticker' => 'bool',
            ],
            'PublishToMobileAction' => [
                'Incidents' => 'bool',
                'HomePage' => 'bool',
            ],
            'PublishToDisplayAction' => [
                'OnPlace' => 'bool',
                'OnBoard' => 'bool',
            ],
            'PublishToTvAction' => [
                'Ceefax' => 'bool',
                'Teletext' => 'bool',
            ],
            'PublishToAlertsAction' => [
                'ClearNotice' => 'bool',
                'ByEmail' => 'bool',
                'ByMobile' => 'bool',
            ],
        ],
    ];

    public function process()
    {
        $start = microtime(true);
        parent::process();
        $this->logDebug(
            "Parsed %s situations in %.3f seconds",
            count($this->situations),
            microtime(true) - $start
        );
    }

    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['SituationExchangeDelivery'], [$this, 'sxDelivery'])
            ->addNestedCallback(['SituationExchangeDelivery', 'SubscriberRef'], [$this, 'readSubscriberRef'])
            ->addNestedCallback(['SituationExchangeDelivery', 'SubscriptionRef'], [$this, 'verifySubscriptionRef'])
            ->addNestedCallback(
                ['SituationExchangeDelivery', 'Situations', 'PtSituationElement'],
                [$this, 'ptSituation']
            );
    }

    /**
     * ChristmasTreeParser callback.
     *
     * Prepare target for SituationExchangeDelivery content.
     */
    public function sxDelivery()
    {
        $this->situations = [];
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function ptSituation()
    {
        $this->assertAuthenticated();
        $xml = $this->reader->expandSimpleXml();
        $mapper = new XmlMapper($xml, static::$ptSituationSchema);
        $this->situations[] = $mapper->execute();
    }
}
