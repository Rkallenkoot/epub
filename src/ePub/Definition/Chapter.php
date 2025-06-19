<?php


namespace ePub\Definition;


class Chapter
{
    public $title;
    public $position;
    public $children;

    public function __construct($title, $pos, public $src = null)
    {
        $this->title = str_replace(["\n", "\r"], ' ', $title);
        $this->position = (int) $pos;
        $this->children = [];
    }


    public function addChild(Chapter $child)
    {
        $this->children[] = $child;
    }
}
