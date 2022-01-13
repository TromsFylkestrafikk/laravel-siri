<?php

namespace TromsFylkestrafikk\Siri\Services;

use Illuminate\Support\Str;
use SimpleXMLElement;
use TromsFylkestrafikk\Siri\Siri;

/**
 * Extract XML elements to an array using an array as schema.
 *
 * The map lists the elements to be extracted from the XML as keys, and the type
 * or children as value. If a map item is a string, it's key will be casted to
 * its type. If the item is an array, it will map the sub-tree of elements to
 * the returned value.
 *
 * If the key '#multiple' is set, it will turn the given element into an array
 * of peers for given element.  Exmple map:
 * @code
 * $map = [
 *   'book' => [
 *     '#multiple' => true,
 *     'Author' => [
 *       'Name' => 'string',
 *       'Age' => 'int',
 *       'Diseased' => 'bool',
 *     ],
 *     'ISBN' => 'string',
 *     'Categories' => [
 *        'Category' => [
 *            '#multiple' => true,
 *            'Name' => 'string',
 *        ],
 *     ],
 *   ],
 * ];
 * @endcode
 */
class XmlMapper
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options Settings for how to extract/map elements.
     */
    public function __construct($options = [])
    {
        $this->options = $options;
    }

    /**
     * Extract XML into an array using a php array as schema.
     *
     * @param array $schema Map of element => type pairs to extract from xml.
     * @param SimpleXMLElement $xml The XML tree to extract values from.
     *
     * @return array
     */
    public function getXmlElements(array $schema, SimpleXMLElement $xml)
    {
        $ret = [];
        foreach (array_keys($schema) as $element) {
            if (strpos($element, '#') === 0) {
                continue;
            }
            $elementVal = $this->getXmlElement($element, $schema, $xml);
            if ($elementVal !== null) {
                $ret[$this->destKey($element)] = $elementVal;
            }
        }
        return $ret;
    }

    protected function destKey($elementName)
    {
        if (empty($this->options['element_case_style'])) {
            return $elementName;
        }
        $caseMethod = $this->options['element_case_style'];
        return Str::$caseMethod($elementName);
    }

    /**
     * The the value of a single element from XML.
     *
     * @param string $element Name of element to get
     * @param array $schema The '$element' argument must be present within this.
     *
     * @return mixed
     */
    protected function getXmlElement($element, $schema, SimpleXMLElement $xml)
    {
        $xml->registerXPathNamespace('siri', Siri::NS);
        $elXml = $xml->xpath("siri:$element");
        if (!count($elXml)) {
            return null;
        }
        if (!is_array($schema[$element])) {
            return static::castValue(trim((string) $elXml[0]), $schema[$element]);
        }
        if (!empty($schema[$element]['#multiple'])) {
            $childItems = [];
            foreach ($elXml as $childXml) {
                $childItems[] = $this->getXmlElements($schema[$element], $childXml);
            }
            return $childItems;
        }
        return $this->getXmlElements($schema[$element], $elXml[0]);
    }

    /**
     * Cast given value to type $cast.
     *
     * @param string $value
     * @param string $cast
     *
     * @return mixed
     */
    public static function castValue($value, $cast)
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
