<?php

namespace TromsFylkestrafikk\Siri\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Simple event allowing other packages to modify schema before parsing.
 */
class ChannelSchema
{
    use Dispatchable;

    /**
     * @var string
     */
    public $channel;

    /**
     * @var array
     */
    public $schema;

    /**
     * @var null|string
     */
    public $elName;

    public function __construct($channel, $schema, $elName = null)
    {
        $this->channel = $channel;
        $this->schema = $schema;
        $this->elName = $elName;
    }
}
