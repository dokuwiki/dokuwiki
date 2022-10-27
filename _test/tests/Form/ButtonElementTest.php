<?php

namespace dokuwiki\test\Form;

use dokuwiki\Form;
use DOMWrap\Document;

class ButtonElementTest extends \DokuWikiTest
{

    function testSimple()
    {
        $form = new Form\Form();
        $form->addButton('foo', 'Hello <b>World</b>')->val('bam')->attr('type', 'submit');

        $html = $form->toHTML();
        $pq = (new Document())->html($html);

        $input = $pq->find('button[name=foo]');
        $this->assertTrue($input->count() == 1);
        $this->assertEquals('bam', $input->attr('value'));
        $this->assertEquals('submit', $input->attr('type'));
        $this->assertEquals('Hello <b>World</b>', $input->text()); // tags were escaped

        $b = $input->find('b'); // no tags found
        $this->assertTrue($b->count() == 0);
    }

    function testHtml()
    {
        $form = new Form\Form();
        $form->addButtonHTML('foo', 'Hello <b>World</b>')->val('bam')->attr('type', 'submit');

        $html = $form->toHTML();
        $pq = (new Document())->html($html);

        $input = $pq->find('button[name=foo]');
        $this->assertTrue($input->count() == 1);
        $this->assertEquals('bam', $input->attr('value'));
        $this->assertEquals('submit', $input->attr('type'));
        $this->assertEquals('Hello World', $input->text()); // tags are stripped here

        $b = $input->find('b'); // tags found
        $this->assertTrue($b->count() == 1);
    }
}
