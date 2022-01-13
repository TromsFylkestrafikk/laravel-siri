<?php

namespace TromsFylkestrafikk\Siri\Helpers;

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
     * @param array $schema Map of key => value pairs to extract from xml.
     * @param SimpleXMLElement $xml The XML tree to extract values from.
     */
    public static function getXmlChildElements(array $schema, SimpleXMLElement $xml)
    {
        $ret = [];
        foreach (array_keys($schema) as $element) {
            if (strpos($element, '#') === 0) {
                continue;
            }
            $elementVal = static::getXmlElement($element, $schema, $xml);
            if ($elementVal !== null) {
                $ret[$element] = $elementVal;
            }
        }
        return $ret;
    }

    protected static function getXmlElement($element, $schema, SimpleXMLElement $xml)
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
                $childItems[] = static::getXmlChildElements($schema[$element], $childXml);
            }
            return $childItems;
        }
        return static::getXmlChildElements($schema[$element], $elXml[0]);
    }

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
