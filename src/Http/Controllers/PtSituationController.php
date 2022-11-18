<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use TromsFylkestrafikk\Siri\Http\Controllers\Controller;
use TromsFylkestrafikk\Siri\Models\Sx\PtSituation;

class PtSituationController extends Controller
{
    /**
     * Get a list of all open, valid SIRI SX Situations.
     *
     * @return \Illuminate\Database\Eloquent\Collection|\TromsFylkestrafikk\Siri\Models\Sx\PtSituation[]
     */
    public function index()
    {
        // @phpstan-ignore-next-line
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
}
