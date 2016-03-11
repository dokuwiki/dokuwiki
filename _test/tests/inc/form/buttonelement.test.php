<?php

use dokuwiki\Form;

class form_buttonelement_test extends DokuWikiTest {

    function test_simple() {
        $form = new Form\Form();
        $form->addButton('foo', 'Hello <b>World</b>')->val('bam')->attr('type', 'submit');

        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $input = $pq->find('button[name=foo]');
        $this->assertTrue($input->length == 1);
        $this->assertEquals('bam', $input->val());
        $this->assertEquals('submit', $input->attr('type'));
        $this->assertEquals('Hello <b>World</b>', $input->text()); // tags were escaped

        $b = $input->find('b'); // no tags found
        $this->assertTrue($b->length == 0);
    }

    function test_html() {
        $form = new Form\Form();
        $form->addButtonHTML('foo', 'Hello <b>World</b>')->val('bam')->attr('type', 'submit');

        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $input = $pq->find('button[name=foo]');
        $this->assertTrue($input->length == 1);
        $this->assertEquals('bam', $input->val());
        $this->assertEquals('submit', $input->attr('type'));
        $this->assertEquals('Hello World', $input->text()); // tags are stripped here

        $b = $input->find('b'); // tags found
        $this->assertTrue($b->length == 1);
    }
}
