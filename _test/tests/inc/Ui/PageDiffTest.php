<?php

namespace dokuwiki\Ui;

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



    // test PageDiff::handle()
    public function testDiffPairing()
    {
        global $INFO;

        // create new page
        $INFO['id'] = $page = 'page';
        $file = wikiFN($page);
        saveWikiText($page, 'teststring', '1st save', false);
        $this->assertFileExists($file);
        $lastmod = filemtime($file);
        $PageDiff = new PageDiff($page);

        $this->doMethod($PageDiff, 'handle');
        $newRev= $this->getProperty($PageDiff, 'newRev');
        $oldRev= $this->getProperty($PageDiff, 'oldRev');

        $this->assertEquals($lastmod, $newRev);
        $this->assertEquals($lastmod, $oldRev);

        // externally delete the page
        unlink($file);
        $PageDiff = new PageDiff($page);
        $this->doMethod($PageDiff, 'handle');
        $newRev= $this->getProperty($PageDiff, 'newRev');
        $oldRev= $this->getProperty($PageDiff, 'oldRev');

        $this->assertNotEquals($lastmod, $newRev);
        $this->assertEquals($lastmod, $oldRev);

        // a page that had not ever existed
        $INFO['id'] = $page = 'noexist';
        $PageDiff = new PageDiff($page);
        $this->doMethod($PageDiff, 'handle');
        $newRev= $this->getProperty($PageDiff, 'newRev');
        $oldRev= $this->getProperty($PageDiff, 'oldRev');

        $this->assertFalse($newRev);
        $this->assertFalse($oldRev);

        unset($PageDiff);
    }

}
