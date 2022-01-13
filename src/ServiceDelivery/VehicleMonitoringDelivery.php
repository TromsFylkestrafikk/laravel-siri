<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;
use TromsFylkestrafikk\Siri\Siri;

class VehicleMonitoringDelivery extends Base
{
    /**
     * @var string
     */
    protected $subscriberRef;

    /**
     * @var string
     */
    protected $subscriptionId;

    /**
     * @var mixed[]
     */
    protected $activities;

    /**
     * Tree of XML elements to harvest.
     *
     * @var array
     */
    public static $activityTree = [
        'RecordedAtTime' => 'string',
        'ProgressBetweenStops' => [
            'LinkDistance' => 'float',
            'Percentage' => 'float',
        ],
        'MonitoredVehicleJourney' => [
            'LineRef' => 'string',
            'FramedVehicleJourneyRef' => [
                'DataFrameRef' => 'string',
                'DatedVehicleJourneyRef' => 'string',
            ],
            'PublishedLineName' => 'string',
            'Monitored' => 'bool',
            'VehicleLocation' => [
                'Latitude' => 'float',
                'Longitude' => 'float',
            ],
            'Bearing' => 'string',
            'Delay' => 'string',
            'VehicleRef' => 'string',
            'PreviousCalls' => [
                'PreviousCall' => [
                    '#multiple' => true,
                    'StopPointRef' => 'string',
                    'VisitNumber' => 'string',
                    'StopPointName' => 'string',
                    'VehicleAtStop' => 'bool',
                ],
            ],
            'MonitoredCall' => [
                'StopPointRef' => 'string',
                'VisitNumber' => 'string',
                'StopPointName' => 'string',
                'VehicleAtStop' => 'bool',
            ],
        ],
    ];

    public function process()
    {
        parent::process();
        dump($this->activities);
    }

    public function setupHandlers()
    {
        $this->reader->addNestedCallback(['VehicleMonitoringDelivery'], [$this, 'vmDelivery'])
            ->addNestedCallback(['VehicleMonitoringDelivery', 'SubscriberRef'], function (ChristmasTreeParser $reader) {
                $this->subscriberRef = trim($reader->readString());
            })
            ->addNestedCallback(['VehicleMonitoringDelivery', 'SubscriptionRef'], function (ChristmasTreeParser $reader) {
                $this->subscriptionId = trim($reader->readString());
            })
            ->addNestedCallback(['VehicleMonitoringDelivery', 'VehicleActivity'], [$this, 'vehicleActivity']);
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function vmDelivery()
    {
        Log::debug("VehicleMonitoring delivery");
        $this->activities = [];
    }

    /**
     * ChristmasTreeParser callback.
     */
    public function vehicleActivity(ChristmasTreeParser $reader)
    {
        $actXml = $reader->expandSimpleXml();
        $this->activities[] = $this->getXmlChildElements(self::$activityTree, $actXml);
        $reader->halt();
    }

    protected function getXmlChildElements(array $map, SimpleXMLElement $xml)
    {
        $ret = [];
        foreach (array_keys($map) as $element) {
            if (strpos($element, '#') === 0) {
                continue;
            }
            $elementVal = $this->getXmlElement($element, $map, $xml);
            if ($elementVal !== null) {
                $ret[$element] = $elementVal;
            }
        }
        return $ret;
    }

    public function getXmlElement($element, $map, SimpleXMLElement $xml)
    {
        $xml->registerXPathNamespace('siri', Siri::NS);
        $elXml = $xml->xpath("siri:$element");
        if (!count($elXml)) {
            return null;
        }
        if (!is_array($map[$element])) {
            return $this->castValue(trim((string) $elXml[0]), $map[$element]);
        }
        if (!empty($map[$element]['#multiple'])) {
            $childItems = [];
            foreach ($elXml as $childXml) {
                $childItems[] = $this->getXmlChildElements($map[$element], $childXml);
            }
            return $childItems;
        }
        return $this->getXmlChildElements($map[$element], $elXml[0]);
    }

    protected function castValue($value, $cast)
    {
        switch ($cast) {
            case 'int':
                return intval($value);
            case 'float':
                return floatval($value);
            case 'string':
                return $value;
            case 'bool':
                return strtolower($value) === 'yes';
        }
    }
}
