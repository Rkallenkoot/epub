<?php

namespace ePub\Tests\Definition;

use ePub\Definition\ManifestItem;
use PHPUnit\Framework\TestCase;

class ManifestItemTest extends TestCase
{

    /**
     * @dataProvider providerProperties
     */
    public function testShouldSetProperties($attribute, $result)
    {
        $item = new ManifestItem;

        $item->setProperties($attribute);

        $this->assertEquals($result, $item->properties);
    }

    public function providerProperties() : array
    {
        return [
            ['', []],
            ['cover-image', ['cover-image']],
            ['rendition:page-spread-center', ['rendition:page-spread-center']],
            ['cover-image rendition:page-spread-center', ['cover-image', 'rendition:page-spread-center']],
        ];
    }
}
