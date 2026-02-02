<?php

namespace ePub\Resource;

interface ResourceInterface
{
    /**
     * Set the current working directory for the resource loader.
     * This is used to resolve relative paths.
     *
     * @param string $dir
     */
    public function setDirectory(string $dir): void;

    /**
     * Get the content of a file from the resource.
     *
     * @param string $path The path to the file.
     * @return string|false The content of the file, or false on failure.
     */
    public function get(string $path): string|false;

    /**
     * Get the content of a file from the resource and parse it as XML.
     *
     * @param string $path The path to the file.
     * @return \SimpleXMLElement|false The parsed XML, or false on failure.
     */
    public function getXML(string $path): \SimpleXMLElement|false;
}
