<?php

namespace TromsFylkestrafikk\Siri\Services;

use Exception;
use Illuminate\Support\Str;

class CaseStyler
{
    protected $style;

    public function __construct(string $style = 'camel')
    {
        $this->style = $style;
        if (! in_array($style, ['studly', 'camel', 'snake', 'kebab'])) {
            throw new Exception("Unknown case style: " . $style);
        }
    }

    /**
     * Get string in configured case style.
     *
     * @param string $str
     *
     * @return string
     */
    public function style(string $str)
    {
        return call_user_func([Str::class, $this->style], $str);
    }
}
