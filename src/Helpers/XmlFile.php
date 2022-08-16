<?php

namespace TromsFylkestrafikk\Siri\Helpers;

use Illuminate\Support\Facades\Storage;

/**
 * Hide the Xml file handling mess.
 */
class XmlFile
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $disk;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $path;

    /**
     * Create new wrapper for handling a single XML file.
     *
     * @param string $filename Siri XML base filename, without path.
     */
    final public function __construct($filename)
    {
        $this->disk = Storage::disk(config('siri.disk'));
        $this->filename = $filename;
        $this->path = config('siri.folder') . '/' . $this->filename;
    }

    /**
     * Replace/set content in XML file.
     *
     * @param string|resource $content
     *
     * @return bool
     */
    public function put($content)
    {
        return $this->disk->put($this->path, $content);
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

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get the full filesystem (or uri) for this xml file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->disk->path($this->path);
    }

    /**
     * Delete file from storage.
     *
     * @return bool
     */
    public function delete()
    {
        return $this->disk->delete($this->path);
    }
}
