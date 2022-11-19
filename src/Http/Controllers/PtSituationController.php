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
     * @return \Illuminate\Database\Eloquent\Collection|\TromsFylkestrafikk\Siri\Models\Sx\PtSituation[]
     */
    public function index()
    {
        return PtSituation::with(['affectedJourneys', 'affectedLines', 'affectedStopPoints'])->get();
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
    public function quaySituations($quayId)
    {
        // @phpstan-ignore-next-line
        return PtSituation::with(['affectedJourneys', 'affectedLines', 'affectedStopPoints'])
            ->whereHas('affectedStopPoints', function (Builder $query) use ($quayId) {
                $query->where('stop_point_ref', $quayId);
            })
            ->get();
    }
}
