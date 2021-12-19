<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use Illuminate\Http\Request;

class DevelEmulateClientController extends Controller
{
    /**
     * Show the XML upload form.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('siri::devel.upload-xml', ['subscriptions' => $this->getSubscriptions()]);
    }

    /**
     * Handle uploaded SIRI Xml data as new request to ourselves.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\View\View
     */
    public function store(Request $request)
    {
        $request->validate(['id' => 'required|exists:siri_subscriptions']);
        $subscription = SiriSubscription::find($request->input('id'));
        switch ($subscription->channel) {
            case 'ET':
                break;
        }
        // $result = $this->processorGate(fopen($request->file('siri-xml')->path(), 'r'));
        return view('siri::devel.upload-xml', ['subscriptions' => $this->getSubscriptions()]);
    }

    protected function getSubscriptions()
    {
        return SiriSubscription::all(['id', 'channel', 'subscription_url']);
    }
}
