<?php

/*
 * This file is part of the ePub Reader package
 *
 * (c) Justin Rainbow <justin.rainbow@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ePub\Definition;

class Package
{
    public $version;

    public $opfDirectory;

    private Metadata $metadata;

    private Manifest $manifest;

    private Spine $spine;

    private Guide $guide;

    private Navigation $navigation;

    private ?PageList $pageList = null;

    public function __construct()
    {
        $this->manifest   = new Manifest();
        $this->metadata   = new Metadata();
        $this->spine      = new Spine();
        $this->guide      = new Guide();
        $this->navigation = new Navigation();
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getManifest(): Manifest
    {
        return $this->manifest;
    }

    public function getSpine(): Spine
    {
        return $this->spine;
    }

    public function getGuide(): Guide
    {
        return $this->guide;
    }

    public function getNavigation(): Navigation
    {
        return $this->navigation;
    }

    public function setPageList(PageList $pageList): void
    {
        $this->pageList = $pageList;
    }

    public function getPageList(): ?PageList
    {
        return $this->pageList;
    }
}
