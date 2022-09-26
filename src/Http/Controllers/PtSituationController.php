<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use TromsFylkestrafikk\Siri\Http\Controllers\Controller;
use TromsFylkestrafikk\Siri\Models\Sx\PtSituation;

class PtSituationController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  PtSituation  $ptSituation
     * @return PtSituation
     */
    public function show(PtSituation $ptSituation)
    {
        return $ptSituation;
    }
}
