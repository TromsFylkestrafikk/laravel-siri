<?php

namespace TromsFylkestrafikk\Siri\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\Siri\Models\SiriSubscription;
use TromsFylkestrafikk\Siri\Helpers\XmlFile;
use TromsFylkestrafikk\Siri\Traits\ServiceDeliveryDispatch;

class BackgroundXmlHandler implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ServiceDeliveryDispatch;

    /**
     * @var int
     */
    protected $subscriptionId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($subscriptionId, XmlFile $xmlFile, $channel)
    {
        $this->subscriptionId = $subscriptionId;
        $this->channel = $channel;
        $this->xmlFile = $xmlFile;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->subscription = SiriSubscription::find($this->subscriptionId);
        Log::debug('Background service delivery handler');
        $this->dispatchServiceDelivery();
    }
}
