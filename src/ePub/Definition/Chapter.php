<?php

namespace ePub\Definition;

class Chapter
{
    public string $title;
    public int $position;
    public ?string $src;
    private array $children = [];
    private $content;

    public function __construct(string $title, int $pos, ?string $src = null)
    {
        $this->title = str_replace(["\n", "\r"], ' ', $title);
        $this->position = $pos;
        $this->src = $src;
    }

    public function addChild(Chapter $child): void
    {
        $this->children[] = $child;
    }

    public function getChildren(): array
    {
        return $this->children;
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
}
