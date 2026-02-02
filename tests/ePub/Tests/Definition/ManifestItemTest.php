<?php

namespace ePub\Tests\Definition;

use ePub\Definition\ManifestItem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ManifestItemTest extends TestCase
{
    #[DataProvider('providerProperties')]
    public function testShouldSetProperties(string $attribute, array $result): void
    {
        $item = new ManifestItem();

        $item->setProperties($attribute);

        $this->assertEquals($result, $item->properties);
    }

    public static function providerProperties(): array
    {
        return [
            'empty string becomes empty array' => ['', []],
            'single property' => ['cover-image', ['cover-image']],
            'single property with colon' => ['rendition:page-spread-center', ['rendition:page-spread-center']],
            'multiple space-separated properties' => ['cover-image rendition:page-spread-center', ['cover-image', 'rendition:page-spread-center']],
        ];
    }
}
