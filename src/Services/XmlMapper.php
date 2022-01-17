<?php

namespace TromsFylkestrafikk\Siri\Services;

use Illuminate\Support\Str;
use Illuminate\Config\Repository;
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
 *   'Book' => [
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
     * @var SimpleXMLElement $xml
     */
    protected $xml;

    /**
     * @var array
     */
    protected $schema;

    /**
     * @var array
     */
    protected $target;

    /**
     * @var null|Repository
     */
    protected $targetRepo;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var bool
     */
    protected $hasMapped;

    /**
     * @param SimpleXMLElement $xml,
     * @param array $schema Array with schema to retrieve.
     * @param array $options Settings for how to extract/map elements.
     */
    public function __construct(SimpleXMLElement $xml, array $schema, $options = [])
    {
        $this->xml = $xml;
        $this->schema = $schema;
        $this->target = [];
        $this->options = array_merge([
            'element_case_style' => config('siri.xml_element_case_style'),
        ], $options);
        $this->targetRepo = null;
    }

    /**
     * Set target/destination array for mapped elements
     *
     * @param mixed[] $target
     */
    public function setTarget(array &$target)
    {
        $this->target = $target;
    }

    /**
     * Perform XML to array mapping and return result.
     *
     * @return array
     */
    public function execute(): array
    {
        if (!$this->hasMapped) {
            $this->target = $this->getXmlElements($this->schema, $this->xml);
            $this->hasMapped = true;
        }
        return $this->target;
    }

    /**
     * Get a value from target array using dot notation.
     *
     * The key can be in any case style, but mixing snake and kebab style seems
     * to confuse Laravel's Str::class case method, so try to be consistent in
     * your choice of case style weapon.
     *
     * To retrieve e.g <DeeplyNested><XmlTreeValue>34</...> you can retrieve it
     * in any of the configurable case style ways, e.g.:
     *   - $this->get('deeplyNested.xmlTreeValue');
     *   - $this->get('deeply_nested.xml_tree_value');
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $this->execute();
        if ($this->targetRepo === null) {
            $this->targetRepo = new Repository($this->target);
        }
        $caseStyle = $this->options['element_case_style'];
        $parts = array_map([Str::class, $caseStyle], explode('.', $key));
        return $this->targetRepo->get(implode('.', $parts), $default);
    }

    /**
     * Given an Xml element name, return the key used in target array.
     *
     * @param string $elementName
     *
     * @return string
     */
    public function destKey(string $elementName): string
    {
        if (empty($this->options['element_case_style'])) {
            return $elementName;
        }
        $caseMethod = $this->options['element_case_style'];
        return Str::$caseMethod($elementName);
    }

    /**
     * Cast given value to type $cast.
     *
     * @param string $value
     * @param string $cast
     *
     * @return mixed
     */
    public static function castValue(string $value, string $cast)
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

    /**
     * Extract XML into an array using a php array as schema.
     *
     * @param array $schema Map of element => type pairs to extract from xml.
     * @param SimpleXMLElement $xml The XML tree to extract values from.
     *
     * @return array
     */
    protected function getXmlElements(array $schema, SimpleXMLElement $xml): array
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
}
