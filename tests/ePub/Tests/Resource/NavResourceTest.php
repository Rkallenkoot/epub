<?php

namespace ePub\Tests\Resource;

use ePub\Definition\Package;
use ePub\Resource\NavResource;
use ePub\Tests\BaseTestCase;

class NavResourceTest extends BaseTestCase
{
    private NavResource $navResource;
    private Package $package;

    protected function setUp(): void
    {
        parent::setUp();
        $navContent = $this->getFixture('epub3/toc.xhtml');
        $this->package = new Package();
        $this->navResource = new NavResource($navContent);
        $this->navResource->bind($this->package);
    }

    public function testTocParsing(): void
    {
        $navigation = $this->package->getNavigation();
        $this->assertCount(3, $navigation->getChapters());

        $chapters = $navigation->getChapters();
        $this->assertEquals('World Cultures and Geography', $chapters[0]->title);
        $this->assertEquals('WCAG-ch1-1.xhtml#d18656e11', $chapters[0]->src);
        $this->assertCount(0, $chapters[0]->getChildren());

        $this->assertEquals('Senior Consultants', $chapters[1]->title);
        $this->assertEquals('WCAG-ch1-1.xhtml#d18656e22', $chapters[1]->src);

        $this->assertEquals('UNIT 1 - INTRODUCTION TO WORLD CULTURES AND GEOGRAPHY', $chapters[2]->title);
        $this->assertEquals('WCAG-ch1-2.xhtml#d18656e63', $chapters[2]->src);
        $this->assertCount(1, $chapters[2]->getChildren());

        $unit1Children = $chapters[2]->getChildren();
        $chapter1 = $unit1Children[0];
        $this->assertEquals('CHAPTER 1 - Welcome to the World', $chapter1->title);
        $this->assertCount(8, $chapter1->getChildren());
    }

    public function testPageListParsing(): void
    {
        $pageList = $this->package->getPageList();
        $this->assertNotNull($pageList);
        $this->assertCount(21, $pageList->all());

        $pages = $pageList->all();

        $firstPage = $pages[0];
        $this->assertEquals('iv', $firstPage->getIdentifier());
        $this->assertEquals('iv', $firstPage->getText());
        $this->assertEquals('WCAG-ch1-1.xhtml#piv', $firstPage->getSrc());

        $lastPage = $pages[20];
        $this->assertEquals('31', $lastPage->getIdentifier());
        $this->assertEquals('31', $lastPage->getText());
        $this->assertEquals('WCAG-ch1-2.xhtml#p31', $lastPage->getSrc());
    }
}
