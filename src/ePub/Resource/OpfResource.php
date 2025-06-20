<?php

namespace ePub\Resource;

use ePub\Definition\Guide;
use ePub\Definition\GuideItem;
use ePub\Definition\Manifest;
use ePub\Definition\ManifestItem;
use ePub\Definition\Metadata;
use ePub\Definition\MetadataItem;
use ePub\Definition\Package;
use ePub\Definition\Spine;
use ePub\Definition\SpineItem;
use ePub\Exception\InvalidArgumentException;
use ePub\Resource\NcxResource;
use Saloon\XmlWrangler\XmlReader;

class OpfResource
{
    private XmlReader $reader;
    private ?ManifestItem $navItem = null;
    private ?ManifestItem $ncxItem = null;

    public function __construct(string $data, private ?ZipFileResource $resource = null)
    {
        $this->reader = XmlReader::fromString($data);
    }

    public function bind(Package $package = null): Package
    {
        $package = $package ?: new Package();

        $this->reader->removeNamespaces();

        $packageElement = $this->reader->element('package')->sole();

        if (!$packageElement) {
            throw new InvalidArgumentException('Could not find the root <package> element in the OPF file.');
        }

        $package->version = $packageElement->getAttribute('version');

        $this->processMetadata($package->getMetadata());
        $this->processManifest($package->getManifest());
        $this->processSpine($package->getSpine(), $package->getManifest());
        $this->processGuide($package->getGuide());
        $this->processNavigation($package);

        return $package;
    }

    private function processMetadata(Metadata $metadata): void
    {
        $metadataArray = $this->reader->value('package.metadata')->sole();

        if (!$metadataArray) {
            return;
        }

        foreach ($metadataArray as $name => $valueOrValues) {
            $values = is_array($valueOrValues) && !isset($valueOrValues['content']) ? $valueOrValues : [$valueOrValues];

            foreach ($values as $value) {
                $item = new MetadataItem();
                $item->name = $name;

                if (is_array($value)) {
                    $item->value = $value['content'] ?? null;
                    $item->attributes = $value['attributes'] ?? [];
                } else {
                    $item->value = (string) $value;
                }

                $metadata->add($item);
            }
        }
    }

    private function processManifest(Manifest $manifest): void
    {
        foreach ($this->reader->element('package.manifest.item')->lazy() as $itemElement) {
            $attributes = $itemElement->getAttributes();

            $item = new ManifestItem(
                $attributes['id'] ?? null,
                $attributes['href'] ?? null,
                $attributes['media-type'] ?? null
            );

            $item->setProperties($attributes['properties'] ?? null);

            if ($item->hasProperty('nav')) {
                $this->navItem = $item;
            }

            if ($item->getMediaType() === 'application/x-dtbncx+xml') {
                $this->ncxItem = $item;
            }

            $this->addContentGetter($item);
            $manifest->add($item);
        }
    }

    private function processSpine(Spine $spine, Manifest $manifest): void
    {
        $spineElement = $this->reader->element('package.spine')->sole();
        if (!$spineElement) {
            return;
        }

        $spine->setPageProgressionDirection($spineElement->getAttribute('page-progression-direction', 'ltr'));

        $position = 1;
        foreach ($this->reader->element('package.spine.itemref')->lazy() as $itemRefElement) {
            $id = $itemRefElement->getAttribute('idref');
            $manifestItem = $manifest->get($id);

            if (!$manifestItem) {
                continue;
            }

            $item = new SpineItem();
            $item->id = $id;
            $item->type = $manifestItem->getMediaType();
            $item->href = $manifestItem->getHref();
            $item->order = $position++;
            $item->linear = $itemRefElement->getAttribute('linear', 'yes');

            $this->addContentGetter($item);
            $spine->add($item);
        }
    }

    private function processGuide(Guide $guide): void
    {
        foreach ($this->reader->element('package.guide.reference')->lazy() as $referenceElement) {
            $item = new GuideItem();
            $attributes = $referenceElement->getAttributes();

            $item->title = $attributes['title'] ?? null;
            $item->type = $attributes['type'] ?? null;
            $item->href = $attributes['href'] ?? null;

            $this->addContentGetter($item);
            $guide->add($item);
        }
    }

    private function processNavigation(Package $package): void
    {
        if ($this->navItem) {
            $content = $this->navItem->getContent();
            if ($content) {
                $navResource = new NavResource($content, $this->resource);
                $navResource->bind($package);
                return;
            }
        }

        $ncxManifestItem = $this->ncxItem;

        if (!$ncxManifestItem) {
            $spineElement = $this->reader->element('package.spine')->sole();
            $tocId = $spineElement?->getAttribute('toc');
            if ($tocId && $package->getManifest()->has($tocId)) {
                $ncxManifestItem = $package->getManifest()->get($tocId);
            }
        }

        if ($ncxManifestItem) {
            $content = $ncxManifestItem->getContent();
            if ($content) {
                $ncxResource = new NcxResource($content, $this->resource);
                $ncxResource->bind($package);
            }
        }
    }

    private function addContentGetter($item): void
    {
        if (null !== $this->resource) {
            $resource = $this->resource;
            $href = method_exists($item, 'getHref') ? $item->getHref() : ($item->href ?? null);
            if ($href && method_exists($item, 'setContent')) {
                $item->setContent(fn() => $resource->get($href));
            }
        }
    }
}
