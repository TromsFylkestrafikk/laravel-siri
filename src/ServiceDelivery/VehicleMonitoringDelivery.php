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
     * Mapping between xpath and destination key in activity array.
     *
     * @var string[]
     */
    public static $xpathMap = [
        '//siri:RecordedAtTime' => 'recordedAtTime',
        '//siri:ProgressBetweenStops/siri:LinkDistance' => 'progressLinkDistance',
        '//siri:ProgressBetweenStops/siri:Percentage' => 'progressPercentage',
        '//siri:MonitoredVehicleJourney/siri:LineRef' => 'lineRef',
        '//siri:MonitoredVehicleJourney/siri:FramedVehicleJourneyRef/siri:DataFrameRef' => 'journeyDataFrameRef',
        '//siri:MonitoredVehicleJourney/siri:FramedVehicleJourneyRef/siri:DatedVehicleJourneyRef' => 'journeyRef',
        '//siri:MonitoredVehicleJourney/siri:PublishedLineName' => 'lineName',
        '//siri:MonitoredVehicleJourney/siri:Monitored' => 'monitored',
        '//siri:MonitoredVehicleJourney/siri:VehicleLocation/siri:Latitude' => 'latitude',
        '//siri:MonitoredVehicleJourney/siri:VehicleLocation/siri:Longitude' => 'longitude',
        '//siri:MonitoredVehicleJourney/siri:Bearing' => 'bearing',
        '//siri:MonitoredVehicleJourney/siri:Delay' => 'delay',
        '//siri:MonitoredVehicleJourney/siri:VehicleRef' => 'vehicleRef',
        '//siri:MonitoredVehicleJourney/siri:MonitoredCall/siri:StopPointRef' => 'callStopPointRef',
        '//siri:MonitoredVehicleJourney/siri:MonitoredCall/siri:VisitNumber' => 'callVisitNumber',
        '//siri:MonitoredVehicleJourney/siri:MonitoredCall/siri:StopPointName' => 'callStopPointName',
        '//siri:MonitoredVehicleJourney/siri:MonitoredCall/siri:VehicleAtStop' => 'callVehicleAtStop',
    ];

    public static $activityTree = [
        'RecordedAtTime' => 'single',
        'ProgressBetweenStops' => [
            'LinkDistance' => 'single',
            'Percentage' => 'single',
        ],
        'MonitoredVehicleJourney' => [
            'LineRef' => 'single',
            'FramedVehicleJourneyRef' => [
                'DataFrameRef' => 'single',
                'DatedVehicleJourneyRef' => 'single',
            ],
            'PublishedLineName' => 'single',
            'Monitored' => 'single',
            'VehicleLocation' => [
                'Latitude' => 'single',
                'Longitude' => 'single',
            ],
            'Bearing' => 'single',
            'Delay' => 'single',
            'VehicleRef' => 'single',
            'PreviousCalls' => [
                'PreviousCall' => [
                    '#multiple' => true,
                    'StopPointRef' => 'single',
                    'VisitNumber' => 'single',
                    'StopPointName' => 'single',
                    'VehicleAtStop' => 'single',
                ],
            ],
            'MonitoredCall' => [
                'StopPointRef' => 'single',
                'VisitNumber' => 'single',
                'StopPointName' => 'single',
                'VehicleAtStop' => 'single',
            ],
        ],
    ];

    public static $xpathPrevCallMap = [
        'siri:StopPointRef' => 'stopPointRef',
        'siri:VisitNumber' => 'visitNumber',
        'siri:StopPointName' => 'stopPointName',
        'siri:VehicleAtStop' => 'vehicleAtStop',
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
        $activity = [];
        $actXml = $reader->expandSimpleXml();
        $actXml->registerXPathNamespace('siri', Siri::NS);
        foreach (self::$xpathMap as $xpath => $destKey) {
            $hits = $actXml->xpath($xpath);
            if (count($hits)) {
                $activity[$destKey] = trim((string) $hits[0]);
            }
        }
        $this->activities[] = $activity;
        $reader->halt();
    }

    public function getXmlElement($element, $map, SimpleXMLElement $xml)
    {
        $xml->registerXPathNamespace('siri', Siri::NS);
        $elXml = $xml->xpath("siri:$element");
        if (!count($elXml)) {
            return null;
        }
        if (!is_array($map[$element])) {
            return trim((string) $elXml[0]);
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

    protected function getXmlChildElements($map, SimpleXMLElement $xml)
    {
        $ret = [];
        foreach ($map as $element => $children) {
            $ret[$element] = $this->getXmlElement($element, $map, $xml);
        }
        return $ret;
    }

    protected function parsePreviousCalls(SimpleXMLElement $xml, &$activity)
    {
        // $actXml->registerXPathNamespace('siri', Siri::NS);
        $prevCalls = [];
        foreach ($xml->xpath('//siri:MonitoredVehicleJourney/siri:PreviousCalls/siri:PreviousCall') as $prev) {
            $call = [];
            $this->xmlToArray($prev, self::$xpathPrevCallMap, $call);
            $prevCalls[] = $call;
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param array $map
     * @param array $dest
     */
    protected function xmlToArray($xml, $map, &$dest)
    {
        $xml->registerXPathNamespace('siri', Siri::NS);
        foreach ($map as $xpath => $destKey) {
            $hits = $xml->xpath($xpath);
            if (count($hits)) {
                $dest[$destKey] = trim((string) $hits[0]);
            }
        }
    }
}
