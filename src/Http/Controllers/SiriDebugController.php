<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use Illuminate\Http\Request;

class SiriDebugController extends Controller
{
    public function subscribeOk(Request $request)
    {
        return "<?xml version=\"1.0\"?>
<Ohai>
</Ohai>
";
    }
}
