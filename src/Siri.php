<?php

namespace TromsFylkestrafikk\Siri;

class Siri
{
    public const NS = 'http://www.siri.org.uk/siri';
    public static $serviceMap = [
        'ET' => 'EstimatedTimetableDelivery',
        'SX' => 'SituationExchangeDelivery',
        'VM' => 'VehicleMonitoringDelivery',
    ];
}
