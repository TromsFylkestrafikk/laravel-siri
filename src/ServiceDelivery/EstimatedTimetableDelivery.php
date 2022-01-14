<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use Illuminate\Support\Facades\Log;

class EstimatedTimetableDelivery extends Base
{
    public function process()
    {
        parent::process();
    }

    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['EstimatedTimetableDelivery'], [$this, 'etDelivery'])
            ->parse()
            ->close();
    }

    public function etDelivery()
    {
        Log::debug("EstimatedTimetable delivery");
    }
}
