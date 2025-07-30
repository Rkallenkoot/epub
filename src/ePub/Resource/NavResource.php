<?php

namespace ePub\Resource;

use ePub\Definition\Chapter;
use ePub\Definition\Manifest;
use ePub\Definition\Package;
use ePub\Definition\PageList;
use ePub\Definition\PageListItem;
use ePub\NamespaceRegistry;

class NavResource
{
    private \DOMDocument $dom;
    private \DOMXPath $xpath;

    public function __construct(string $data, private ?ZipFileResource $resource = null)
    {
        $this->dom = new \DOMDocument();
        @$this->dom->loadXML($data, LIBXML_NOWARNING | LIBXML_NOERROR);
        $this->xpath = new \DOMXPath($this->dom);

        foreach (NamespaceRegistry::getNamespaces() as $prefix => $uri) {
            $this->xpath->registerNamespace($prefix, $uri);
        }
        $this->xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
    }

    public function bind(Package $package): Package
    {
        $this->processToc($package);
        $this->processPageList($package);

        return $package;
    }

    private function processToc(Package $package): void
    {
        $tocNav = $this->xpath->query('//xhtml:nav[@epub:type="toc"]')->item(0);
        if (!$tocNav) {
            return;
        }

        $chapters = $this->processNavList($tocNav, $package->getManifest());
        foreach ($chapters as $chapter) {
            $package->getNavigation()->addChapter($chapter);
        }
    }

    private function processNavList(\DOMElement $element, Manifest $manifest, int &$order = 1): array
    {
        $chapters = [];
        /** @var \DOMNodeList $listItems */
        $listItems = $this->xpath->query('./xhtml:ol/xhtml:li', $element);

        foreach ($listItems as $listItem) {
            /** @var \DOMElement|null $link */
            $link = $this->xpath->query('./xhtml:a|./xhtml:span', $listItem)->item(0);
            if (!$link) {
                continue;
            }

            $href = $link->attributes->getNamedItem('href')?->nodeValue;
            $title = trim($link->nodeValue);
            $chapter = new Chapter($title, $order++, $href);

            if ($this->resource && $href) {
                $this->addContentGetter($chapter, $href, $manifest);
            }

            $childChapters = $this->processNavList($listItem, $manifest, $order);
            foreach ($childChapters as $childChapter) {
                $chapter->addChild($childChapter);
            }

            $chapters[] = $chapter;
        }

        return $chapters;
    }

    private function processPageList(Package $package): void
    {
        $pageListNav = $this->xpath->query('//xhtml:nav[@epub:type="page-list"]')->item(0);
        if (!$pageListNav) {
            return;
        }

        $pageList = new PageList();
        $listItems = $this->xpath->query('.//xhtml:li', $pageListNav);

        foreach ($listItems as $listItem) {
            /** @var \DOMElement|null $link */
            $link = $this->xpath->query('./xhtml:a', $listItem)->item(0);
            if (!$link) {
                continue;
            }

            $href = $link->attributes->getNamedItem('href')?->nodeValue;
            $pageNumber = trim($link->nodeValue);

            // Extract the fragment (page identifier) from the href
            $fragment = null;
            if ($href && str_contains($href, '#')) {
                $parts = explode('#', $href);
                $fragment = $parts[1] ?? null;
            }

            $pageListItem = new PageListItem(
                $fragment ?? $href, 
                $href, 
                $pageNumber
            );
            $pageList->add($pageListItem);
        }

        if (count($pageList->all()) > 0) {
            $package->setPageList($pageList);
        }
    }

    private function addContentGetter(Chapter $item, string $href, Manifest $manifest): void
    {
        $parts = parse_url($href);
        $path = $parts['path'] ?? '';

        foreach ($manifest->all() as $manifestItem) {
            if ($manifestItem->getHref() === $path) {
                $item->setContent(fn() => $manifestItem->getContent());
                break;
            }
        }
    }
}
