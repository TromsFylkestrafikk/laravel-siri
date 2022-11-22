<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use TromsFylkestrafikk\Siri\Http\Controllers\Controller;
use TromsFylkestrafikk\Siri\Models\Sx\PtSituation;

class PtSituationController extends Controller
{
    /**
     * List all open, valid SIRI SX Situations.
     *
     * @return mixed[]
     */
    public function index()
    {
        return [
            'status' => 'ok',
            'situations' => PtSituation::with(['affectedJourneys', 'affectedLines', 'affectedStopPoints', 'stopPoints'])->get(),
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  PtSituation  $ptSituation
     *
     * @return PtSituation
     */
    public function show(PtSituation $ptSituation)
    {
        return $ptSituation;
    }

    /**
     * List all situations related to a given quay.
     *
     * @param string $quayId
     *
     * @return \Illuminate\Database\Eloquent\Collection|\TromsFylkestrafikk\Siri\Models\Sx\PtSituation[]
     */
    public function situationsQuay($quayId)
    {
        return PtSituation::with(['affectedJourneys', 'affectedLines', 'affectedStopPoints'])
            ->whereHas('affectedStopPoints', fn (Builder $query) => $query->where('stop_point_ref', $quayId))
            ->get();
    }

    public function situationsLine($lineId)
    {
        return PtSituation::with(['affectedJourneys', 'affectedLines', 'affectedStopPoints'])
            ->whereHas('affectedLines', fn (Builder $query) => $query->where('line_ref', $lineId))
            ->get();
    }

    public function situationsJourney($journeyId)
    {
        return PtSituation::with(['affectedJourneys', 'affectedLines', 'affectedStopPoints'])
            ->whereHas('affectedJourneys', fn (Builder $query) => $query->where('journey_ref', $journeyId))
            ->get();
    }
}
