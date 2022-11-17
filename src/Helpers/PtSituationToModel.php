<?php

namespace TromsFylkestrafikk\Siri\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\Siri\Models\Sx\PtSituation;
use TromsFylkestrafikk\Siri\Models\Sx\InfoLink;
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

    protected $startTime = 0;

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
        $this->startTime = microtime(true);
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
        $this->time("Situation to Models BEGIN");
        InfoLink::where('pt_situation_id', $this->situation->id)->delete();
        AffectedJourney::where('pt_situation_id', $this->situation->id)->delete();
        AffectedLine::where('pt_situation_id', $this->situation->id)->delete();
        AffectedRoute::where('pt_situation_id', $this->situation->id)->delete();
        AffectedStopPoint::where('pt_situation_id', $this->situation->id)->delete();
        $this->time("Previous models removed");
        $this->storeLinks();
        $this->storeAffectedJourneys();
        $this->processAffectedNetworks();
        if (!empty($this->rawSit['affects']['stop_points']['affected_stop_point'])) {
            $this->storeAffectedStopPoints($this->rawSit['affects']['stop_points']['affected_stop_point']);
        }
        $this->time("Situation to Models COMPLETE");

        return $this->situation;
    }

    public static function dbSafeDate($dateStr, $format = 'Y-m-d H:i:s')
    {
        $date = new Carbon($dateStr);
        $date->tz = config('app.timezone');
        return $date->format($format);
    }

    protected function storeLinks()
    {
        if (empty($this->rawSit['info_links']['info_link'])) {
            return;
        }
        foreach ($this->rawSit['info_links']['info_link'] as $rawLink) {
            InfoLink::create(array_merge($rawLink, ['pt_situation_id' => $this->situation->id]));
        }
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
            AffectedJourney::create([
                'id' => $this->createId($this->situation->id, $journeyRef, $dataFrameRef),
                'pt_situation_id' => $this->situation->id,
                'journey_ref' => $journeyRef,
                'data_frame_ref' => $dataFrameRef,
            ]);
            if (!empty($rawJourney['route']['stop_points']['affected_stop_point'])) {
                $this->storeAffectedStopPoints($rawJourney['route']['stop_points']['affected_stop_point']);
            }
        }
        $this->time("Affected journeys stored");
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
        $this->time("Affected lines BEGIN");
        foreach ($rawLines as $rawLine) {
            $aLine = AffectedLine::create([
                'id' => $this->createId($this->situation->id, $rawLine['line_ref']),
                'pt_situation_id' => $this->situation->id,
                'line_ref' => $rawLine['line_ref'],
            ]);
            if (!empty($rawLine['routes']['affected_route'])) {
                $this->storeAffectedRoutes($rawLine['routes']['affected_route'], $aLine);
            }
        }
        $this->time("Affected lines END");
    }

    protected function storeAffectedRoutes($rawRoutes, AffectedLine $aLine = null)
    {
        $this->time("Affected routes BEGIN");
        foreach ($rawRoutes as $rawRoute) {
            $aRoute = empty($rawRoute['route_ref']) ? null : AffectedRoute::create([
                'id' => $this->createId($this->situation->id, $rawRoute['route_ref']),
                'pt_situation_id' => $this->situation->id,
                'route_ref' => $rawRoute['route_ref'] ?? null,
            ]);
            if (!empty($rawRoute['stop_points']['affected_stop_point'])) {
                $this->storeAffectedStopPoints($rawRoute['stop_points']['affected_stop_point']);
            }
        }
        $this->time("Affected routes END");
    }

    protected function storeAffectedStopPoints($rawStops)
    {
        $this->time("Affected stop points BEGIN");
        foreach ($rawStops as $rawStop) {
            AffectedStopPoint::updateOrCreate([
                'id' => $this->createId($this->situation->id, $rawStop['stop_point_ref']),
            ], [
                'pt_situation_id' => $this->situation->id,
                'stop_point_ref' => $rawStop['stop_point_ref'],
                'stop_condition' => $rawStop['stop_condition'] ?? null,
            ]);
        }
        $this->time("Affected stop points END");
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

    protected static function createId(...$keys)
    {
        $key = implode('.', $keys);
        if (strlen($key) > 64) {
            $key = md5($key);
        }
        return $key;
    }

    protected function time($msg, ...$args)
    {
        $tillNow = microtime(true) - $this->startTime;
        $printArgs = ["Exec time: %.3f %s", $tillNow, $msg, ...$args];
        Log::debug(call_user_func_array('sprintf', $printArgs));
    }
}
