<?php

namespace tests\inc\Ui;

use dokuwiki\ChangeLog\PageChangeLog;
use dokuwiki\Ui\PageDiff;
use \ReflectionClass;

/**
 * Class PageDiffTest
 *
 */
class PageDiffTest extends \DokuWikiTest
{
    /**
     * Exec non-public methods
     * @oaram object $obj
     * @param string $methodName
     * @param array $param arguments to pass the method
     * @return mixed
     * @throws \ReflectionException
     */
    private function doMethod($obj, $methodName, array $param = [])
    {
        $reflection = new \ReflectionClass($obj);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $param);
    }

    /**
     * Get non-public property
     * @oaram object $obj
     * @param string $propertyName
     * @return mixed
     * @throws \ReflectionException
     */
    private function getProperty($obj, $propertyName)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

    /**
     * Set non-public property
     * @oaram object $obj
     * @param string $propertyName
     * @param mixed $value
     * @return void
     * @throws \ReflectionException
     */
    private function setProperty($obj, $propertyName)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($obj, $value);
    }


    /**
     * test PageDiff::handle() when external deletion happened
     */
    public function testRevisionPair_ExternalDelete()
    {
        global $INFO;

        // a page that had not ever existed
        $INFO['id'] = $page = 'page';
        $file = wikiFN($page);
        $this->assertFileNotExists($file);

        $PageDiff = new PageDiff($page);
        $this->doMethod($PageDiff, 'handle');
        $revisions = $this->getProperty($PageDiff, 'revisions');

        $this->assertFalse($revisions[1]);
        $this->assertFalse($revisions[0]);

        // create new page
        saveWikiText($page, 'teststring', '1st save', false);
        $this->assertFileExists($file);
        $newmod = filemtime($file);

        $PageDiff = new PageDiff($page);
        $this->doMethod($PageDiff, 'handle');
        $revisions = $this->getProperty($PageDiff, 'revisions');

        $this->assertEquals($newmod, $revisions[1]);
        $this->assertFalse($revisions[0]);    // because just created
        $lastmod =$newmod;

        // externally delete the page
        $this->waitForTick(); // wait for new revision ID
        unlink($file);

        $PageDiff = new PageDiff($page);
        $this->doMethod($PageDiff, 'handle');
        $revisions = $this->getProperty($PageDiff, 'revisions');

        $this->assertNotEquals($lastmod, $revisions[1]);
        $this->assertEquals($lastmod, $revisions[0]);

        unset($PageDiff);
    }

    /**
     * test PageDiff::handle() when external edit happened
     */
    public function testRevisionPair_ExternalEdit()
    {
        global $INFO;

        // create new page2
        $INFO['id'] = $page = 'page2';
        $file = wikiFN($page);
        saveWikiText($page, 'teststring', '1st save', false);
        $this->assertFileExists($file);
        $newmod = filemtime($file);

        $PageDiff = new PageDiff($page);
        $this->doMethod($PageDiff, 'handle');
        $revisions = $this->getProperty($PageDiff, 'revisions');

        $this->assertEquals($newmod, $revisions[1]);
        $this->assertFalse($revisions[0]);    // because just created
        $lastmod =$newmod;

        // externally edit
        $this->waitForTick(); // wait for new revision ID
        file_put_contents($file, 'teststring external edit');
        clearstatcache(false, $file);
        $newmod = filemtime($file);

        $PageDiff = new PageDiff($page);
        $this->doMethod($PageDiff, 'handle');
        $revisions = $this->getProperty($PageDiff, 'revisions');

        $this->assertEquals($newmod, $revisions[1]);
        $this->assertEquals($lastmod, $revisions[0]);

        unset($PageDiff);
    }

}
