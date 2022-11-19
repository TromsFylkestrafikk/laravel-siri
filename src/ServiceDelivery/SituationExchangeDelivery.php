<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Events\SxSituations;
use TromsFylkestrafikk\Siri\Events\SxPtSituation;
use TromsFylkestrafikk\Siri\Events\SxRoadSituation;
use TromsFylkestrafikk\Siri\Helpers\PtSituationToModel;

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
        'Source' => [
            'SourceType' => 'string',
            'Name' => 'string',
            'Phone' => 'string',
            'AgentReference' => 'string',
            'TimeOfCommunication' => 'string',
        ],
        'VersionedAtTime' => 'timestamp',
        'Progress' => 'string',
        'ValidityPeriod' => [
            'StartTime' => 'string',
            'EndTime' => 'string',
        ],
        'UndefinedReason' => 'string',
        'Severity' => 'string',
        'Priority' => 'int',
        'ReportType' => 'string',
        'Planned' => 'bool',
        'Summary' => 'string',
        'Description' => 'string',
        'Advice' => 'string',
        'InfoLinks' => [
            'InfoLink' => [
                '#multiple' => true,
                'Uri' => 'string',
                'Label' => 'string',
            ],
        ],
        'Affects' => [
            'Networks' => [
                'AffectedNetwork' => [
                    '#multiple' => true,
                    'NetworkRef' => 'string',
                    'AffectedOperator' => [
                        'OperatorRef' => 'string',
                    ],
                    'VehicleMode' => 'string',
                    'AirSubmode' => 'string',
                    'BusSubmode' => 'string',
                    'Coach' => 'string',
                    'MetroSubmode' => 'string',
                    'RailSubmode' => 'string',
                    'TramSubmode' => 'string',
                    'WaterSubmode' => 'string',
                    'AllLines' => 'string',
                    'AffectedLine' => [
                        '#multiple' => true,
                        'LineRef' => 'string',
                        'Routes' => [
                            'AffectedRoute' => [
                                '#multiple' => true,
                                'RouteRef' => 'string',
                                'StopPoints' => [
                                    'AffectedOnly' => 'bool',
                                    'AffectedStopPoint' => [
                                        '#multiple' => true,
                                        'StopPointRef' => 'string',
                                        'StopCondition' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'StopPoints' => [
                'AffectedStopPoint' => [
                    '#multiple' => true,
                    'StopPointRef' => 'string',
                    'StopCondition' => 'string',
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
                            'ComponentType' => 'string',
                            'AccessFeatureType' => 'string',
                        ],
                    ],
                ],
            ],
            'VehicleJourneys' => [
                'AffectedVehicleJourney' => [
                    '#multiple' => true,
                    'VehicleJourneyRef' => 'string',
                    'DatedVehicleJourneyRef' => 'string',
                    'FramedVehicleJourneyRef' => [
                        'DataFrameRef' => 'string',
                        'DatedVehicleJourneyRef' => 'string',
                    ],
                    'LineRef' => 'string',
                    'Route' => [
                        '#multiple' => true,
                        'RouteRef' => 'string',
                        'StopPoints' => [
                            'AffectedOnly' => 'bool',
                            'AffectedStopPoint' => [
                                '#multiple' => true,
                                'StopPointRef' => 'string',
                                'StopCondition' => 'string',
                            ],
                        ],
                    ],
                    'OriginAimedDepartureTime' => 'string',
                ],
            ],
        ],
    ];

    public static $roadSituationSchema = [
        //
    ];

    protected $willEmitPayload = true;

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

    /**
     * @inheritdoc
     */
    protected function postProcess()
    {
        $this->willEmitPayload = true;
        foreach ($this->ptSituations as $rawSit) {
            $archiver = PtSituationToModel::store($rawSit, $this->responseTimestamp);
            if (!$archiver->valid) {
                $this->willEmitPayload = false;
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function emitPayload()
    {
        if (!$this->willEmitPayload) {
            return false;
        }
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
