<?php

namespace tests\inc\Extension;

use dokuwiki\Extension\Event;

class EventTest extends \DokuWikiTest
{
    static public function staticFunc(&$data)
    {
        $data['test'] = strtoupper($data['test']);
    }

    public function dynamicFunc(&$data)
    {
        $data['test'] = strtoupper($data['test']);
    }

    public function testGlobal()
    {
        $data = 'test';
        $result = Event::createAndTrigger('TESTTRIGGER', $data, 'strtoupper');
        $this->assertEquals('TEST', $result);
    }

    public function testDynamic()
    {
        $data = ['test' => 'test'];
        Event::createAndTrigger('TESTTRIGGER', $data, [$this, 'dynamicFunc']);
        $this->assertEquals(['test' => 'TEST'], $data);
    }

    public function testStatic()
    {
        $data = ['test' => 'test'];
        Event::createAndTrigger('TESTTRIGGER', $data, 'tests\inc\Extension\EventTest::staticFunc');
        $this->assertEquals(['test' => 'TEST'], $data);

        $data = ['test' => 'test'];
        Event::createAndTrigger('TESTTRIGGER', $data, ['tests\inc\Extension\EventTest', 'staticFunc']);
        $this->assertEquals(['test' => 'TEST'], $data);
    }
}
