<?php

namespace ePub\Resource;

use Saloon\XmlWrangler\XmlReader;
use ePub\Definition\Package;
use ePub\Definition\Metadata;
use ePub\Definition\MetadataItem;
use ePub\Definition\Manifest;
use ePub\Definition\ManifestItem;
use ePub\Definition\Spine;
use ePub\Definition\SpineItem;
use ePub\Definition\Guide;
use ePub\Definition\GuideItem;
use ePub\Definition\Navigation;
use ePub\Exception\InvalidArgumentException;

class OpfResource
{
    private XmlReader $reader;

    public function __construct(string $data, private ?ZipFileResource $resource = null)
    {
        $this->reader = XmlReader::fromString($data);
    }

    public function bind(Package $package = null): Package
    {
        $package = $package ?: new Package();

        // The key to simplicity: remove all namespaces!
        $this->reader->removeNamespaces();

        // Find the root <package> element. sole() will throw an exception if not found.
        $packageElement = $this->reader->element('package')->sole();

        if (!$packageElement) {
            throw new InvalidArgumentException('Could not find the root <package> element in the OPF file.');
        }

        $package->version = $packageElement->getAttribute('version');

        // Process each section
        $this->processMetadata($package->metadata);
        $this->processManifest($package->manifest);
        $this->processSpine($package->spine, $package->manifest, $package->navigation);
        $this->processGuide($package->guide);

        return $package;
    }

    private function processMetadata(Metadata $metadata): void
    {
        // Get the entire metadata block as an array of values.
        $metadataArray = $this->reader->value('package.metadata')->sole();

        if (!$metadataArray) {
            return;
        }

        foreach ($metadataArray as $name => $valueOrValues) {
            // Handle multiple tags with the same name, e.g., <meta> or <dc:date>
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
        // Use a memory-efficient lazy iterator for all <item> elements.
        foreach ($this->reader->element('package.manifest.item')->lazy() as $itemElement) {
            $item = new ManifestItem();
            $attributes = $itemElement->getAttributes();

            $item->id       = $attributes['id'] ?? null;
            $item->href     = $attributes['href'] ?? null;
            $item->type     = $attributes['media-type'] ?? null;
            $item->fallback = $attributes['fallback'] ?? null;
            $item->setProperties($attributes['properties'] ?? null);

            $this->addContentGetter($item);
            $manifest->add($item);
        }
    }

    private function processSpine(Spine $spine, Manifest $manifest, Navigation $navigation): void
    {
        // Use a lazy iterator for all <itemref> elements.
        $position = 1;
        foreach ($this->reader->element('package.spine.itemref')->lazy() as $itemRefElement) {
            $id = $itemRefElement->getAttribute('idref');
            $manifestItem = $manifest->get($id);

            if (!$manifestItem) continue;

            $item = new SpineItem();
            $item->id     = $id;
            $item->type   = $manifestItem->type;
            $item->href   = $manifestItem->href;
            $item->order  = $position++;
            $item->linear = $itemRefElement->getAttribute('linear', 'yes');

            $this->addContentGetter($item);
            $spine->add($item);
        }

        // Get the 'toc' attribute from the spine element itself
        $spineElement = $this->reader->element('package.spine')->sole();
        $ncxId = $spineElement->getAttribute('toc', 'ncx');

        if ($manifest->has($ncxId)) {
            $navigation->src = $manifest->get($ncxId);
        }
    }

    private function processGuide(Guide $guide): void
    {
        // Use a lazy iterator for all <reference> elements.
        foreach ($this->reader->element('package.guide.reference')->lazy() as $referenceElement) {
            $item = new GuideItem();
            $attributes = $referenceElement->getAttributes();

            $item->title = $attributes['title'] ?? null;
            $item->type  = $attributes['type'] ?? null;
            $item->href  = $attributes['href'] ?? null;

            $this->addContentGetter($item);
            $guide->add($item);
        }
    }

    private function addContentGetter($item): void
    {
        if (null !== $this->resource) {
            $resource = $this->resource;
            $item->setContent(fn() => $resource->get($item->href));
        }
    }
}
