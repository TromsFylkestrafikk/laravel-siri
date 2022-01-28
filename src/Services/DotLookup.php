<?php

namespace TromsFylkestrafikk\Siri\Services;

use Illuminate\Config\Repository;

/**
 * Lookup mapped arrays using dot notation.
 *
 * Ease the pain of looking up values in nested arrays and check for existence,
 * and in the same time, consider the case style used.
 */
class DotLookup
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $repo;

    /**
     * @var \TromsFylkestrafikk\Siri\Services\CaseStyler
     */
    protected $case;

    /**
     * @param mixed[] $map
     */
    public function __construct(array $map)
    {
        $this->repo = new Repository($map);
        $this->case = app('siri.case');
    }

    /**
     * Get a value using dot notation.
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
        $parts = array_map([$this->case, 'style'], explode('.', $key));
        return $this->repo->get(implode('.', $parts), $default);
    }
}
