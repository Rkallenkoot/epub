<?php

namespace ePub\Resource;

use Saloon\XmlWrangler\XmlReader;
use Saloon\XmlWrangler\Data\Element;
use ePub\Definition\Package;
use ePub\Definition\Chapter;
use ePub\Exception\InvalidArgumentException;

class NcxResource
{
    private XmlReader $reader;

    /**
     * Constructor
     *
     * @param string $data The raw XML content of the NCX file.
     * @param ZipFileResource|null $resource The Zip resource for lazy-loading content.
     */
    public function __construct(string $data, private ?ZipFileResource $resource = null)
    {
        $this->reader = XmlReader::fromString($data);
    }

    /**
     * Processes the NCX XML data and populates the navigation chapters in a Package object.
     *
     * @param Package|null $package
     * @return Package
     * @throws InvalidArgumentException
     */
    public function bind(Package $package = null): Package
    {
        $package = $package ?: new Package();

        // The key to simplicity: remove all namespaces!
        $this->reader->removeNamespaces();

        // Find the root <navMap> element.
        $navMapElement = $this->reader->element('ncx.navMap')->sole();

        if (! $navMapElement) {
            // An NCX file without a navMap is not useful for navigation.
            return $package;
        }

        // Start the recursive processing of navigation points (chapters).
        $this->processNavPoints($package->navigation->chapters);

        return $package;
    }

    /**
     * Iterates over <navPoint> elements within a given parent element and adds them
     * to the chapters array.
     *
     * @param XmlReader $parentElement The parent element containing navPoints (e.g., navMap or another navPoint).
     * @param array $chapters The array to add the new Chapter objects to.
     */
    private function processNavPoints(array &$chapters): void
    {
        foreach ($this->reader->element('ncx.navMap.navPoint')->lazy() as $navPoint) {
            $chapters[] = $this->consumeNavPoint($navPoint);
        }
    }

    /**
     * Recursively consumes a <navPoint> element, creates a Chapter object,
     * and processes any nested navPoints.
     *
     * @param Element $navPointElement The Element DTO for the current <navPoint>.
     * @return Chapter
     */
    private function consumeNavPoint(Element $navPointElement): ?Chapter
    {
        $navPointContent = $navPointElement->getContent();
        $navLabel = $navPointContent['navLabel'] ?? null;

        $title = $navLabel?->getContent()['text']->getContent() ?? '';
        $content = $navPointContent['content'] ?? null;
        $order = (int) $navPointElement->getAttribute('playOrder');

        $chapter = new Chapter($title, $order, $content?->getAttributes()['src']);

        $this->addContentGetter($chapter);

        // Check for nested <navPoint> children within the content array.
        if (isset($navPointContent['navPoint'])) {
            $childrenData = $navPointContent['navPoint'];

            foreach ($childrenData->getContent() as $childElement) {
                $chapter->addChild($this->consumeNavPoint($childElement));
            }
        }

        return $chapter;
    }

    /**
     * Attaches a lazy-loading closure to an item for retrieving its content from the Zip archive.
     *
     * This method assumes the provided item has `href` and `setContent` members.
     * For a Chapter, the `src` property would be used as the `href`.
     *
     * @param object $item An object like ManifestItem or Chapter.
     */
    private function addContentGetter(object $item): void
    {
        // To make this fully functional, the Chapter class should have a public `href`
        // property (populated from its `src`) and a `setContent` method.
        if (null !== $this->resource && property_exists($item, 'href') && method_exists($item, 'setContent')) {
            $resource = $this->resource;
            $item->setContent(fn() => $resource->get($item->href));
        }
    }
}
