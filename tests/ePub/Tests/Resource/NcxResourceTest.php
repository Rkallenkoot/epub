<?php

namespace ePub\Tests\Resource;

use ePub\Resource\NcxResource;
use ePub\Tests\BaseTestCase;

class NcxResourceTest extends BaseTestCase
{
    public function testExtractingChaptersFromNcx()
    {
        $epub = $this->getFixtureEpub('the_velveteen_rabbit.epub');

        $this->assertCount(5, $epub->navigation->chapters);
        $this->assertEquals("List of Illustrations", $epub->navigation->chapters[2]->title);
    }

    public function testNestedNavigationNcx()
    {
        $fixture = $this->getFixture('basic/OEPS/nested.ncx');

        $ncx = new NcxResource($fixture);

        $package = $ncx->bind();

        $navigation = $package->getNavigation();

        $this->assertCount(5, $navigation->chapters);
        $this->assertCount(4, $navigation->chapters[4]->children);
    }
}
