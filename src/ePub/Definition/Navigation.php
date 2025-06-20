<?php

namespace ePub\Definition;

class Navigation implements \IteratorAggregate, \Countable
{
    /**
     * Array of Chapters
     *
     * @var Chapter[]
     */
    private array $chapters = [];

    public function addChapter(Chapter $chapter): void
    {
        $this->chapters[] = $chapter;
    }

    /**
     * @return Chapter[]
     */
    public function getChapters(): array
    {
        return $this->chapters;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->chapters);
    }

    public function count(): int
    {
        return count($this->chapters);
    }
}
