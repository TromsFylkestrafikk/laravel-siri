<?php

namespace TromsFylkestrafikk\Siri\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
     * True if incoming situation is newer than existing in DB.
     *
     * @var bool
     */
    public $valid = true;

    /**
     * @var mixed[]
     */
    protected $rawSit = [];

    /**
     * @var string
     */
    protected $responseTimestamp;

    /**
     * @var float
     */
    protected $startTime;

    /**
     * @var PtSituation
     */
    protected $situation;

    /**
     * @param array $rawSit
     */
    final public function __construct(array $rawSit, $responseTimestamp)
    {
        $this->rawSit = $rawSit;
        $this->responseTimestamp = $responseTimestamp;
        $this->startTime = microtime(true);
    }

    /**
     * Instantiate and store raw situation to models.
     *
     * @param mixed[] $rawSit
     *
     * @return PtSituationToModel
     */
    public static function store(array $rawSit, $responseTimestamp)
    {
        $instance = new static($rawSit, $responseTimestamp);
        $instance->toModels();
        return $instance;
    }

    /**
     * Store situation to respective models.
     *
     * @return null|PtSituation
     */
    public function toModels()
    {
        if (!$this->prepareSituation()) {
            $this->valid = false;
            return null;
        }
        InfoLink::where('pt_situation_id', $this->situation->id)->delete();
        AffectedJourney::where('pt_situation_id', $this->situation->id)->delete();
        AffectedLine::where('pt_situation_id', $this->situation->id)->delete();
        AffectedRoute::where('pt_situation_id', $this->situation->id)->delete();
        AffectedStopPoint::where('pt_situation_id', $this->situation->id)->delete();
        DB::table('siri_sx_stoppable')->where('pt_situation_id', $this->situation->id)->delete();

        $this->storeLinks();
        $this->storeAffectedJourneys();
        $this->processAffectedNetworks();
        if (!empty($this->rawSit['affects']['stop_points']['affected_stop_point'])) {
            $this->storeAffectedStopPoints(
                $this->rawSit['affects']['stop_points']['affected_stop_point'],
                $this->situation
            );
        }

        return $this->situation;
    }

    public static function dbSafeDate($dateStr, $format = 'Y-m-d H:i:s')
    {
        $date = new Carbon($dateStr);
        $date->tz = config('app.timezone');
        return $date->format($format);
    }

    /**
     * Get an updated situation based on raw data or null if raw data is old.
     *
     * @return bool
     */
    protected function prepareSituation()
    {
        $this->prepareRawSit();
        $this->situation = PtSituation::withoutGlobalScopes()->find($this->rawSit['situation_number']);
        if (!$this->situation) {
            $this->situation = PtSituation::create($this->rawSit);
            return true;
        }
        if ((new Carbon($this->responseTimestamp))->isBefore(new Carbon($this->situation->response_timestamp))) {
            Log::notice("[PtSituationToModel]: Existing situation is more recent than incoming data. Not updating.");
            return false;
        }
        $this->situation->fill($this->rawSit);
        $this->situation->save();
        return true;
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
            $aJourney = AffectedJourney::create([
                'id' => $this->createId($this->situation->id, $journeyRef, $dataFrameRef),
                'pt_situation_id' => $this->situation->id,
                'journey_ref' => $journeyRef,
                'data_frame_ref' => $dataFrameRef,
            ]);
            if (!empty($rawJourney['route'])) {
                $this->storeAffectedRoutes($rawJourney['route'], $aJourney);
            }
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
            $aId = $this->createId($this->situation->id, $rawLine['line_ref']);
            $aLine = AffectedLine::firstOrCreate(['id' => $aId], [
                'id' => $this->createId($this->situation->id, $rawLine['line_ref']),
                'pt_situation_id' => $this->situation->id,
                'line_ref' => $rawLine['line_ref'],
            ]);
            if (!empty($rawLine['routes']['affected_route'])) {
                $this->storeAffectedRoutes($rawLine['routes']['affected_route'], $aLine);
            }
        }
    }

    protected function storeAffectedRoutes($rawRoutes, $parent)
    {
        foreach ($rawRoutes as $rawRoute) {
            if (!empty($rawRoute['route_ref'])) {
                AffectedRoute::create([
                    'id' => $this->createId($this->situation->id, $rawRoute['route_ref']),
                    'pt_situation_id' => $this->situation->id,
                    'route_ref' => $rawRoute['route_ref'] ?? null,
                ]);
            }
            if (!empty($rawRoute['stop_points']['affected_stop_point'])) {
                $this->storeAffectedStopPoints($rawRoute['stop_points']['affected_stop_point'], $parent);
            }
        }
    }

    /**
     * @param mixed[] $rawStops
     * @param PtSituation|AffectedLine|AffectedJourney $parent
     */
    protected function storeAffectedStopPoints($rawStops, $parent)
    {
        foreach ($rawStops as $rawStop) {
            $aStop = AffectedStopPoint::updateOrCreate([
                'id' => $this->createId($this->situation->id, $rawStop['stop_point_ref']),
            ], [
                'pt_situation_id' => $this->situation->id,
                'stop_point_ref' => $rawStop['stop_point_ref'],
            ]);
            $parent->affectedStopPoints()->attach($aStop->id, [
                'pt_situation_id' => $this->situation->id,
                'stop_condition' => $rawStop['stop_condition'] ?? null,
            ]);
        }
    }

    protected function prepareRawSit()
    {
        $this->rawSit['id'] = $this->rawSit['situation_number'];
        $this->rawSit['creation_time'] = self::dbSafeDate($this->rawSit['creation_time']);
        $this->rawSit['response_timestamp'] = self::dbSafeDate($this->responseTimestamp);
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
}
