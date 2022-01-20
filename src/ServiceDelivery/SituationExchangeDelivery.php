<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Services\XmlMapper;
use TromsFylkestrafikk\Siri\Events\SxSituations;
use TromsFylkestrafikk\Siri\Events\SxPtSituation;
use TromsFylkestrafikk\Siri\Events\SxRoadSituation;

class SituationExchangeDelivery extends Base
{
    /**
     * @var string
     */
    protected $subscriberRef;

    /**
     * @var mixed[]
     */
    protected $ptSituations;

    /**
     * @var mixed[]
     */
    protected $roadSituations;

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

    public static $roadSituationSchema = [
        //
    ];

    /**
     * @var int
     */
    protected $situationsCount;

    /**
     * @inheritdoc
     */
    public function process()
    {
        $start = microtime(true);
        parent::process();
        $this->emitSituations();
        $this->logDebug(
            "Parsed %s situations in %.3f seconds",
            $this->situationsCount,
            microtime(true) - $start
        );
    }

    /**
     * @inheritdoc
     */
    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['SituationExchangeDelivery'], [$this, 'sxDelivery'])
            ->addNestedCallback(['SituationExchangeDelivery', 'SubscriberRef'], [$this, 'readSubscriberRef'])
            ->addNestedCallback(['SituationExchangeDelivery', 'SubscriptionRef'], [$this, 'verifySubscriptionRef'])
            ->addNestedCallback(
                ['SituationExchangeDelivery', 'Situations', 'PtSituationElement'],
                [$this, 'parsePtSituation']
            )
            ->addNestedCallback(
                ['SituationExchangeDelivery', 'Situations', 'RoadSituationElement'],
                [$this, 'parseRoadSituation']
            );
    }

    /**
     * ChristmasTreeParser callback.
     *
     * Prepare target for SituationExchangeDelivery content.
     */
    public function sxDelivery()
    {
        $this->situationsCount = 0;
        $this->resetChunk();
    }

    /**
     * @param string $situationType
     *
     * @return mixed[]
     */
    protected function parseSituationType($situationType)
    {
        $schemas = [
            'road' => static::$roadSituationSchema,
            'point' => static::$ptSituationSchema,
        ];
        $this->assertAuthenticated();
        $xml = $this->reader->expandSimpleXml();
        $mapper = new XmlMapper($xml, $schemas[$situationType]);
        $this->chunkCount++;
        $this->situationsCount++;
        return $mapper->execute();
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function parsePtSituation()
    {
        $situation = $this->parseSituationType('point');
        SxPtSituation::dispatch($this->subscription->id, $situation, $this->subscriberRef, $this->producerRef);
        $this->ptSituations[] = $situation;
        $this->maybeEmitSituations();
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function parseRoadSituation()
    {
        $situation = $this->parseSituationType('road');
        SxRoadSituation::dispatch($this->subscription->id, $situation, $this->subscriberRef, $this->producerRef);
        $this->roadSituations[] = $situation;
        $this->maybeEmitSituations();
    }

    protected function resetChunk()
    {
        $this->ptSituations = [];
        $this->roadSituations = [];
        $this->chunkCount = 0;
    }

    protected function maybeEmitSituations()
    {
        if ($this->maxChunkSize && $this->chunkCount >= $this->maxChunkSize) {
            $this->emitSituations();
            $this->resetChunk();
        }
    }

    protected function emitSituations()
    {
        SxSituations::dispatch(
            $this->subscription->id,
            $this->ptSituations,
            $this->roadSituations,
            $this->subscriberRef,
            $this->producerRef
        );
    }
}
