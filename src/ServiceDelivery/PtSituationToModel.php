<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Models\Sx\PtSituation;
use Illuminate\Support\Carbon;

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
        $this->situation = PtSituation::updateOrCreate(['situation_number' => $sitNr], $this->rawSit);
        $this->storeAffectedJourneys();

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
        foreach ($this->rawSit['affects']['vehicle_journeys'] as $rawJourney) {
            $journeyRef = $rawJourney['framed_vehicle_journey_ref']['dated_vehicle_journey_ref']
                ?? $rawJourney['dated_vehicle_journey_ref']
                ?? $rawJourney['vehicle_journey_ref'];
        }
        return $this;
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
