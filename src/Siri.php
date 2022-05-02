<?php

namespace TromsFylkestrafikk\Siri;

class Siri
{
    /**
     * @var string
     */
    public const NS = 'http://www.siri.org.uk/siri';

    public const VERSIONS = ['1.4', '2.0'];

    /**
     * Map of channels to XML element names below ServiceDelivery.
     *
     * @var string[]
     */
    public static $serviceMap = [
        'ET' => 'EstimatedTimetableDelivery',
        'SX' => 'SituationExchangeDelivery',
        'VM' => 'VehicleMonitoringDelivery',
    ];
}
