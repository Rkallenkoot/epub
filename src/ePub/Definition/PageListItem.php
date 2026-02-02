<?php

namespace ePub\Definition;

class PageListItem implements ItemInterface
{
    public function __construct(
        private string $identifier,
        public ?string $src = null,
        public ?string $text = null
    ) {
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getSrc(): ?string
    {
        return $this->src;
    }

    public function getText(): ?string
    {
        return $this->text;
    }
}
