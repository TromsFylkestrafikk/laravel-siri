<?php

namespace TromsFylkestrafikk\Siri\Helpers;

use Illuminate\Support\Facades\Storage;

class XmlFile
{
    protected $disk;
    protected $filename;
    protected $path;

    final public function __construct($filename)
    {
        $this->disk = Storage::disk(config('siri.disk'));
        $this->filename = $filename;
        $this->path = config('siri.folder') . '/' . $this->filename;
    }

    public function put($content)
    {
        $this->disk->put($this->path, $content);
    }

    /**
     * Create a new file.
     *
     * @param string $type Siri type as acronym
     *
     * @return \TromsFylkestrafikk\Siri\Helpers\XmlFile
     */
    public static function create($type = '')
    {
        $base = "siri";
        if ($type) {
            $base .= '-' . strtolower($type);
        }
        $timestamp = date('Y-m-d\TH:i:s');
        $disk = Storage::disk(config('siri.disk'));
        $folder = config('siri.folder');
        $filename = sprintf("%s-%s.xml", $base, $timestamp);

        $counter = 0;
        while ($disk->exists("{$folder}/{$filename}")) {
            $filename = sprintf("%s-%s_%02d.xml", $base, $timestamp, ++$counter);
        }
        return new static($filename);
    }

    public function getPath()
    {
        return $this->disk->path($this->path);
    }
}
