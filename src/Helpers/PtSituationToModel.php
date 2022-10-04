<?php

namespace TromsFylkestrafikk\Siri\Helpers;

use Illuminate\Support\Carbon;
use TromsFylkestrafikk\Siri\Models\Sx\PtSituation;
use TromsFylkestrafikk\Siri\Models\Sx\AffectedJourney;
use TromsFylkestrafikk\Siri\Models\Sx\AffectedLine;
use TromsFylkestrafikk\Siri\Models\Sx\AffectedRoute;
use TromsFylkestrafikk\Siri\Models\Sx\AffectedStopPoint;

/**
 * Store a single PtSituation to persistent storage.
 */
class PtSituationToModel
{
    /**
     * @var mixed[]
     */
    protected $rawSit;

    /**
     * @var PtSituation
     */
    protected $situation;

    /**
     * @param array $rawSit
     */
    final public function __construct(array $rawSit)
    {
        $this->rawSit = $rawSit;
    }

    /**
     * Instantiate and store raw situation to models.
     *
     * @param mixed[] $rawSit
     *
     * @return PtSituationToModel
     */
    public static function store(array $rawSit)
    {
        $instance = new static($rawSit);
        $instance->toModels();
        return $instance;
    }

    /**
     * Store situation to respective models.
     *
     * @return PtSituation
     */
    public function toModels()
    {
        $sitNr = $this->rawSit['situation_number'];
        $this->prepareRawSit();
        $this->situation = PtSituation::updateOrCreate(['id' => $sitNr], $this->rawSit);
        AffectedJourney::where('pt_situation_id', $this->situation->id)->delete();
        AffectedLine::where('pt_situation_id', $this->situation->id)->delete();
        AffectedRoute::where('pt_situation_id', $this->situation->id)->delete();
        AffectedStopPoint::where('pt_situation_id', $this->situation->id)->delete();
        $this->storeAffectedJourneys();
        $this->processAffectedNetworks();
        if (!empty($this->rawSit['affects']['stop_points']['affected_stop_point'])) {
            $this->storeAffectedStopPoints($this->rawSit['affects']['stop_points']['affected_stop_point']);
        }

        return $this->situation;
    }

    public static function dbSafeDate($dateStr, $format = 'Y-m-d H:i:s')
    {
        $date = new Carbon($dateStr);
        $date->tz = config('app.timezone');
        return $date->format($format);
    }

    protected function storeAffectedJourneys()
    {
        if (empty($this->rawSit['affects']['vehicle_journeys'])) {
            return $this;
        }
        foreach ($this->rawSit['affects']['vehicle_journeys']['affected_vehicle_journey'] as $rawJourney) {
            $journeyRef = $rawJourney['framed_vehicle_journey_ref']['dated_vehicle_journey_ref']
                ?? $rawJourney['dated_vehicle_journey_ref']
                ?? $rawJourney['vehicle_journey_ref'];
            $dataFrameRef = !empty($rawJourney['framed_vehicle_journey_ref'])
                ? $rawJourney['framed_vehicle_journey_ref']['data_frame_ref']
                : null;
            $aJourney = AffectedJourney::create([
                'pt_situation_id' => $this->situation->id,
                'journey_ref' => $journeyRef,
                'data_frame_ref' => $dataFrameRef,
            ]);
        }
        return $this;
    }

    protected function processAffectedNetworks()
    {
        $networks = $this->rawSit['affects']['networks']['affected_network'] ?? null;
        if (!$networks) {
            return;
        }
        foreach ($networks as $network) {
            if (empty($network['affected_line'])) {
                continue;
            }
            $this->storeAffectedLines($network['affected_line']);
        }
    }

    protected function storeAffectedLines($rawLines)
    {
        foreach ($rawLines as $rawLine) {
            $aLine = AffectedLine::create([
                'pt_situation_id' => $this->situation->id,
                'line_ref' => $rawLine['line_ref'],
            ]);
            if (!empty($rawLine['routes']['affected_route'])) {
                $this->storeAffectedRoutes($rawLine['routes']['affected_route'], $aLine);
            }
        }
    }

    protected function storeAffectedRoutes($rawRoutes, AffectedLine $aLine = null)
    {
        foreach ($rawRoutes as $rawRoute) {
            $aRoute = AffectedRoute::create([
                'pt_situation_id' => $this->situation->id,
                'route_ref' => $rawRoute['route_ref'] ?? null,
                'affected_line_id' => $aLine->id,
            ]);
            if (!empty($rawRoute['stop_points']['affected_stop_point'])) {
                $this->storeAffectedStopPoints($rawRoute['stop_points']['affected_stop_point'], $aRoute);
            }
        }
    }

    protected function storeAffectedStopPoints($rawStops, $aRoute = null)
    {
        foreach ($rawStops as $rawStop) {
            $aStop = new AffectedStopPoint();
            $aStop->pt_situation_id = $this->situation->id;
            $aStop->fill($rawStop);
            if ($aRoute) {
                $aStop->affected_route_id = $aRoute->id;
            }
            $aStop->save();
        }
    }

    protected function prepareRawSit()
    {
        $this->rawSit['creation_time'] = self::dbSafeDate($this->rawSit['creation_time']);
        if (isset($this->rawSit['source']['source_type'])) {
            $this->rawSit['source_type'] = $this->rawSit['source']['source_type'];
        }
        if (isset($this->rawSit['source']['name'])) {
            $this->rawSit['source_name'] = $this->rawSit['source']['name'];
        }
        if (isset($this->rawSit['validity_period']['start_time'])) {
            $this->rawSit['validity_start'] = self::dbSafeDate($this->rawSit['validity_period']['start_time']);
        }
        if (isset($this->rawSit['validity_period']['end_time'])) {
            $this->rawSit['validity_end'] = self::dbSafeDate($this->rawSit['validity_period']['end_time']);
        }
    }
}
