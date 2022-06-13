<?php

namespace TromsFylkestrafikk\Siri\Services;

use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Log;
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
     * @var bool
     */
    protected $hasMapped;

    /**
     * Map of namespace => prefix in use during parsing.
     *
     * @var string[]
     */
    protected $namespaces;

    /**
     * @param SimpleXMLElement $xml,
     * @param array $schema Array with schema to retrieve.
     */
    public function __construct(SimpleXMLElement $xml, array $schema)
    {
        $this->xml = $xml;
        $this->schema = $schema;
        $this->target = [];
        $this->targetRepo = null;
        $this->namespaces = [Siri::NS => 'siri'];
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
    protected function getXmlElements(array $schema, SimpleXMLElement $xml, $namespace = null): array
    {
        if (!$namespace) {
            $namespace = Siri::NS;
        }
        $ret = [];
        $caseStyler = app('siri.case');
        foreach (array_keys($schema) as $element) {
            if (strpos($element, '#') === 0) {
                continue;
            }
            $elementVal = $this->getXmlElement($element, $schema, $xml, $namespace);
            if ($elementVal !== null) {
                $ret[$caseStyler->style($element)] = $elementVal;
            }
        }
        return $ret;
    }

    /**
     * The value of a single element from XML.
     *
     * @param string $element Name of element to get
     * @param array $schema The '$element' argument must be present within this.
     * @param SimpleXMLElement $xml Where to extract value from.
     * @param string $namespace Namespace the XML element is using.
     *
     * @return mixed
     */
    protected function getXmlElement($element, $schema, SimpleXMLElement $xml, $namespace)
    {
        $namespace = $this->updateNamespace($schema[$element], $namespace);
        $nsPrefix = $this->getNsPrefix($namespace);
        $xml->registerXPathNamespace($nsPrefix, $namespace);
        $elXml = $xml->xpath("$nsPrefix:$element");
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
        return $this->getXmlElements($schema[$element], $elXml[0], $namespace);
    }

    /**
     * Update namespace used for current element
     *
     * @param mixed[] $elSchema XmlMapper element schema
     * @param string $namespace Parent element namespace
     *
     * @return string
     */
    protected function updateNamespace($elSchema, $namespace)
    {
        if (is_array($elSchema) && !empty($elSchema['#xmlns'])) {
            $namespace = $elSchema['#xmlns'];
        }
        return $namespace;
    }

    /**
     * Get or create a suitable namespace prefix.
     *
     * @param string $namespace
     *
     * @return string New or existing prefix for given namespace.
     */
    protected function getNsPrefix($namespace)
    {
        if (!isset($this->namespaces[$namespace])) {
            $this->namespaces[$namespace] = 'tftmap' . chr(97 + count($this->namespaces));
        }
        return $this->namespaces[$namespace];
    }
}
