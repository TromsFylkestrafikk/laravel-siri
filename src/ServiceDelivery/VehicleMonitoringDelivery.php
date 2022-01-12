<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use Illuminate\Support\Facades\Log;

class VehicleMonitoringDelivery extends Base
{
    public function process()
    {
        parent::process();
        $this->reader->addCallback(['Siri', 'ServiceDelivery', 'VehicleMonitoringDelivery'], [$this, 'vmDelivery'])
            ->parse()
            ->close();
    }

    public function vmDelivery()
    {
        Log::debug("VehicleMonitoring delivery");
    }
}
