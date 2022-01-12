<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use Illuminate\Support\Facades\Log;

class EstimatedTimetableDelivery extends Base
{
    public function process()
    {
        parent::process();
        $this->reader->addCallback(['Siri', 'ServiceDelivery', 'EstimatedTimetableDelivery'], [$this, 'etDelivery'])
            ->parse()
            ->close();
    }

    public function etDelivery()
    {
        Log::debug("EstimatedTimetable delivery");
    }
}
