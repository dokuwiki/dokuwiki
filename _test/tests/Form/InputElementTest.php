<?php

namespace dokuwiki\test\Form;

use dokuwiki\Form;
use DOMWrap\Document;

class InputElementTest extends \DokuWikiTest {

    function testDefaults() {
        $form = new Form\Form();
        $form->addTextInput('foo', 'label text')->val('this is text');

        $html = $form->toHTML();
        $pq = (new Document())->html($html);

        $input = $pq->find('input[name=foo]');
        $this->assertTrue($input->count() == 1);
        $this->assertEquals('this is text', $input->attr('value'));
        $this->assertEquals('text', $input->attr('type'));

        $label = $pq->find('label');
        $this->assertTrue($label->count() == 1);
        $this->assertEquals('label text', $label->find('span')->text());
    }

    /**
     * check that posted values overwrite preset default
     */
    function testPrefill() {
        global $INPUT;
        $INPUT->post->set('foo', 'a new text');

        $form = new Form\Form();
        $form->addTextInput('foo', 'label text')->val('this is text');

        $html = $form->toHTML();
        $pq = (new Document())->html($html);

        $input = $pq->find('input[name=foo]');
        $this->assertTrue($input->count() == 1);
        $this->assertEquals('a new text', $input->attr('value'));
    }

    function test_prefill_empty() {
        global $INPUT;
        $INPUT->post->set('foo', '');

        $form = new Form\Form();
        $form->addTextInput('foo', 'label text')->val('this is text');

        $html = $form->toHTML();
        $pq = (new Document())->html($html);

        $input = $pq->find('input[name=foo]');
        $this->assertTrue($input->count() == 1);
        $this->assertEquals('', $input->attr('value'));
    }


    function test_password() {
        $form = new Form\Form();
        $form->addPasswordInput('foo', 'label text')->val('this is text');

        $html = $form->toHTML();
        $pq = (new Document())->html($html);

        $input = $pq->find('input[name=foo]');
        $this->assertTrue($input->count() == 1);
        $this->assertEquals('this is text', $input->attr('value'));
        $this->assertEquals('password', $input->attr('type'));

        $label = $pq->find('label');
        $this->assertTrue($label->count() == 1);
        $this->assertEquals('label text', $label->find('span')->text());
    }
}
