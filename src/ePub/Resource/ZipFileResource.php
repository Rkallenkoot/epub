<?php

/*
 * This file is part of the ePub Reader package
 *
 * (c) Justin Rainbow <justin.rainbow@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ePub\Resource;

use ZipArchive;

class ZipFileResource implements ResourceInterface
{
    private $zipFile;

    private $cwd;

    public function __construct($file)
    {
        $this->zipFile = new \ZipArchive();

        $this->zipFile->open($file);
    }

    public function setDirectory(string $dir): void
    {
        $this->cwd = $dir;
    }

    public function get(string $path): string|false
    {
        if (null !== $this->cwd) {
            $path = $this->cwd . "/" . $path;
        }

        return $this->zipFile->getFromName($path);
    }

    public function getXML(string $path): \SimpleXMLElement|false
    {
        return simplexml_load_string($this->get($path));
    }

    public function all()
    {
        $result = [];

        for ($i = 0; $i < $this->zipFile->numFiles; $i++) {
            $item = $this->zipFile->statIndex($i);

            $result[] = $item["name"];
        }

        return $result;
    }
}
