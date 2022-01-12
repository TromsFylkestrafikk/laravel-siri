<?php

namespace TromsFylkestrafikk\Siri\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Use printf syntax for writing log entries with optional prefix.
 */
trait LogPrefix
{
    /**
     * Use this as prefix for all log entries.
     *
     * @var string
     */
    protected $logPrefix = '';

    /**
     * available log levels.
     */
    protected $logLevels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    /**
     * Create log prefix.
     *
     * @param string $prefix String in printf format.
     * @param mixed ...$prefixArgs supplementary arguments to printf
     */
    public function setLogPrefix($prefix = '', ...$prefixArgs)
    {
        $this->logPrefix = call_user_func_array('sprintf', [$prefix, ...$prefixArgs]);
    }

    public function logDebug($string, ...$args)
    {
        $this->logPrintf('debug', $string, $args);
    }

    public function logInfo($string, ...$args)
    {
        $this->logPrintf('info', $string, $args);
    }

    public function logNotice($string, ...$args)
    {
        $this->logPrintf('notice', $string, $args);
    }

    public function logWarning($string, ...$args)
    {
        $this->logPrintf('warning', $string, $args);
    }

    public function logError($string, ...$args)
    {
        $this->logPrintf('error', $string, $args);
    }

    public function logCritical($string, ...$args)
    {
        $this->logPrintf('critical', $string, $args);
    }

    public function logAlert($string, ...$args)
    {
        $this->logPrintf('alert', $string, $args);
    }

    public function logEmergency($string, ...$args)
    {
        $this->logPrintf('emergency', $string, $args);
    }

    protected function logPrintf($level, $string, $args = [])
    {
        if (!in_array($level, $this->logLevels)) {
            return;
        }
        Log::$level($this->logPrefix . call_user_func_array('sprintf', [$string, ...$args]));
    }
}
