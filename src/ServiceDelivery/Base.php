<?php

namespace TromsFylkestrafikk\Siri\ServiceDelivery;

use TromsFylkestrafikk\Siri\Helpers\XmlFile;
use TromsFylkestrafikk\Xml\ChristmasTreeParser;

abstract class Base
{
    /**
     * @var string
     */
    public $producerRef;

    /**
     * @var XmlFile
     */
    protected $xmlFile;

    /**
     * @var ChristmasTreeParser
     */
    protected $reader;

    /**
     * @param XmlFile $xmlFile The incoming Siri XML file to process.
     */
    public function __construct(XmlFile $xmlFile)
    {
        $this->xmlFile = $xmlFile;
    }

    /**
     * Process XML file.
     */
    public function process()
    {
        $this->reader = new ChristmasTreeParser();
        $this->reader->open($this->xmlFile->getPath());
        $this->reader->addCallback(['Siri', 'ServiceDelivery'], [$this, 'setupHandlers'])
            ->addCallback(['Siri', 'ServiceDelivery', 'ProducerRef'], function ($reader) {
                $this->producerRef = $reader->readString();
            })
            ->parse()
            ->close();
    }

    abstract public function setupHandlers();
}
