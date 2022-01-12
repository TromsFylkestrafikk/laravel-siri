<?php

namespace TromsFylkestrafikk\Siri\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use TromsFylkestrafikk\Siri\Helpers\XmlFile;
use TromsFylkestrafikk\Siri\Siri;

class BackgroundXmlHandler implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var XmlFile $xmlfile
     */
    protected $xmlFile;

    /**
     * @var string
     */
    protected $channel;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(XmlFile $xmlFile, $channel)
    {
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
        Log::debug('Background service delivery handler');
        $handlerClass = sprintf("\\TromsFylkestrafikk\\Siri\\ServiceDelivery\\%s", Siri::$serviceMap[$this->channel]);
        /** @var \TromsFylkestrafikk\Siri\ServiceDelivery\Base $handler */
        $handler = new $handlerClass($this->xmlFile);
        $handler->process();
    }
}
