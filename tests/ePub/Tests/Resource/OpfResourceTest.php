<?php

/*
 * This file is part of the ePub Reader package
 *
 * (c) Justin Rainbow <justin.rainbow@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ePub\Tests\Resource;

use ePub\Tests\BaseTestCase;
use ePub\Resource\OpfResource;
use ePub\Definition\Metadata;
use ePub\Definition\Manifest;

class OpfResourceTest extends BaseTestCase
{
    public function testLoadingValidOpenPackagingFormatFile()
    {
        $fixture = $this->getFixture('basic/OEPS/content.opf');

        $opf = new OpfResource($fixture);

        $package = $opf->bind();

        $metadata = $package->getMetadata();
        $this->assertTrue($metadata instanceof Metadata);
        $this->assertTrue($metadata->has('title'));
        $this->assertEquals('Epub Format Construction Guide', $metadata->getValue('title'));

        $manifest = $package->getManifest();
        $this->assertTrue($manifest instanceof Manifest);
        $this->assertEquals(
            ["ncx", "css", "logo", "title", "contents", "intro", "part1", "part2", "part3", "part4", "specs"],
            $manifest->keys()
        );
    }

    public function testLoadingAsdValidOpenPackagingFormatFile()
    {
        $fixture = $this->getFixture('basic/OEPS/test.opf');

        $opf = new OpfResource($fixture);

        $package = $opf->bind();

        $metadata = $package->getMetadata();
        $this->assertTrue($metadata instanceof Metadata);
        $this->assertTrue($metadata->has('title'));
        $this->assertEquals('Women Shall Not Rule', $metadata->getValue('title'));

        $manifest = $package->getManifest();
        $this->assertTrue($manifest instanceof Manifest);
        $expectedKeys = ["ncx", 'nav', 'title.html', 'cover', 'css-file', 'd42e8'];
        $manifestKeys = $manifest->keys();

        foreach ($expectedKeys as $key) {
            $this->assertContains($key, $manifestKeys);
        }
    }

    public function testInvalidOpenPackagingFormatInput()
    {
        $this->expectException(\TypeError::class);

        new OpfResource(null);
    }
}
