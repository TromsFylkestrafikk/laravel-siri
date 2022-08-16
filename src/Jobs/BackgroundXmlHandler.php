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
use TromsFylkestrafikk\Siri\Services\ServiceDeliveryDispatcher;

class BackgroundXmlHandler implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var int
     */
    protected $subscriptionId;

    /**
     * @var string
     */
    protected $xmlFilename;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($subscriptionId, $xmlFilename)
    {
        $this->subscriptionId = $subscriptionId;
        $this->xmlFilename = $xmlFilename;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $xmlFile = new XmlFile($this->xmlFilename);
        $subscription = SiriSubscription::find($this->subscriptionId);
        $courier = new ServiceDeliveryDispatcher($subscription, $xmlFile);
        Log::debug('Background service delivery handler');
        $courier->dispatch();
    }
}
