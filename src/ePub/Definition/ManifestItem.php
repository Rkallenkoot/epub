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

class ManifestItem implements ItemInterface
{
    private $content;

    public function __construct(
        public ?string $id = null,
        public ?string $href = null,
        public ?string $type = null,
        public ?array $properties = [],
    ) {}

    public function getIdentifier(): ?string
    {
        return $this->id;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }

    public function getContent()
    {
        if (is_callable($this->content)) {
            $func = $this->content;
            $this->content = $func();
        }

        return $this->content;
    }

    public function setProperties($properties): void
    {
        if (empty($properties)) {
            $this->properties = [];
        } elseif (is_array($properties)) {
            $this->properties = $properties;
        } else {
            $this->properties = explode(' ', (string) $properties);
        }
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function hasProperty(string $property): bool
    {
        return in_array($property, $this->properties, true);
    }

    public function getHref(): ?string
    {
        return $this->href;
    }

    public function getMediaType(): ?string
    {
        return $this->type;
    }
}
