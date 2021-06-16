<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use Illuminate\Http\Request;

class DevelEmulateClientController extends Controller
{
    /**
     * Show the XML upload form.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('siri::devel.upload-xml', ['subscriptions' => $this->getSubscriptions()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $channel = $request->input('siri_channel');
        switch ($channel) {
            case 'ET':
                $this->queuedHandling = true;
                break;
        }
        // $result = $this->processorGate(fopen($request->file('siri-xml')->path(), 'r'));
        return view('devel.upload-xml', ['subscriptions' => $this->getSubscriptions()]);
    }

    protected function getSubscriptions()
    {
        return SiriSubscription::all(['id', 'channel', 'subscription_url']);
    }
}
