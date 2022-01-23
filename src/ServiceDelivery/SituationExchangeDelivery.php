<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Events\SxSituations;
use TromsFylkestrafikk\Siri\Events\SxPtSituation;
use TromsFylkestrafikk\Siri\Events\SxRoadSituation;

class SituationExchangeDelivery extends Base
{
    /**
     * @var mixed[]
     */
    protected $ptSituations;

    /**
     * @var mixed[]
     */
    protected $roadSituations;

    /**
     * Tree of XML elements to harvest for PtSituationElement
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

    public static $roadSituationSchema = [
        //
    ];

    /**
     * @inheritdoc
     */
    protected function getTargetSchema($elName)
    {
        return [
            'PtSituationElement' => static::$ptSituationSchema,
            'RoadSituationElement' => static::$roadSituationSchema,
        ][$elName];
    }

    /**
     * @inheritdoc
     */
    public function setupHandlers()
    {
        $this->reader
            ->addNestedCallback(['Situations', 'PtSituationElement'], [$this, 'parsePtSituation'])
            ->addNestedCallback(['Situations', 'RoadSituationElement'], [$this, 'parseRoadSituation']);
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function parsePtSituation()
    {
        $situation = $this->processChannelPayloadElement();
        $this->ptSituations[] = $situation;
        SxPtSituation::dispatch($this->subscription->id, $this->createPayload('PtSituationElement', $situation));
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function parseRoadSituation()
    {
        $situation = $this->processChannelPayloadElement();
        $this->roadSituations[] = $situation;
        SxRoadSituation::dispatch($this->subscription->id, $this->createPayload('RoadSituationElement', $situation));
    }

    protected function emitPayload()
    {
        $case = app('siri.case');
        SxSituations::dispatch(
            $this->subscription->id,
            $this->createPayload('Situations', [
                $case->style('PtSituationElement') => $this->ptSituations,
                $case->style('RoadSituationElement') => $this->roadSituations,
            ])
        );
        // Reset SX internal harvesters.
        $this->roadSituations = [];
        $this->ptSituations = [];
    }
}
