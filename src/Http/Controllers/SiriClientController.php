<?php

namespace TromsFylkestrafikk\Siri\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;

class SiriClientController extends Controller
{
    /**
     * SIRI channel for our request.
     *
     * @var string
     */
    protected $channel;

    /**
     * Generated filename for incoming SIRI XML.
     *
     * @var string
     */
    protected $filename;

    public function consume(Request $request, $channel, SiriSubscription $subscription)
    {
        $this->channel = $channel;
        $this->saveXml($request);
        $reader = new ChristmasTreeParser();
        $reader->open(Storage::disk(config('siri.disk'))->path($this->filename));
        $reader->addCallback(['Siri', 'SubscriptionResponse'], [$this, 'subscriptionResponse'])
            ->addCallback(['Siri', 'HeartbeatNotification'], [$this, 'heartbeatNotification'])
            ->addCallback(['Siri', 'ServiceDelivery'], [$this, 'serviceDelivery'])
            ->parse()
            ->close();
        return sprintf("Got request of type %s on subscription '%s'", $channel, $subscription->id);
    }

    protected function saveXml(Request $request)
    {
        $this->filename = $this->makeXmlFilename();
        $incoming = $request->getContent(true);
        Storage::disk(config('siri.disk'))->put($this->filename, $incoming);
    }

    /**
     * Generate a unique filename on our disk for incoming XML.
     *
     * @return string
     */
    protected function makeXmlFilename()
    {
        $timestamp = date('Y-m-d\TH:i:s');
        $disk = Storage::disk(config('siri.disk'));
        $folder = config('siri.folder');
        $filename = sprintf("%s/siri-%s-%s.xml", $folder, strtolower($this->channel), $timestamp);

        $counter = 0;
        while ($disk->exists($filename)) {
            $filename = sprintf("%s/%s-%s_%02d.xml", $folder, strtolower($this->channel), $timestamp, ++$counter);
        }
        return $filename;
    }

    public function subscriptionResponse(ChristmasTreeParser $reader)
    {
        Log::debug("Got subscription response");
    }

    public function heartbeatNotification(ChristmasTreeParser $reader)
    {
        Log::debug("Got heartbeat notification response");
    }

    public function serviceDelivery(ChristmasTreeParser $reader)
    {
        Log::debug("Got service delivery. Actual content.");
    }
}
