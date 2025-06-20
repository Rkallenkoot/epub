<?php

namespace ePub\Definition;

class PageList extends Collection
{
    /**
     * Adds an item to the collection using a numeric index.
     *
     * @param ItemInterface $item The item to add.
     */
    public function add(ItemInterface $item): void
    {
        $this->items[] = $item;
    }
}
