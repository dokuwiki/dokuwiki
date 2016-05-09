<?php

use dokuwiki\Form;

class form_inputelement_test extends DokuWikiTest {

    function test_defaults() {
        $form = new Form\Form();
        $form->addTextInput('foo', 'label text')->val('this is text');

        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $input = $pq->find('input[name=foo]');
        $this->assertTrue($input->length == 1);
        $this->assertEquals('this is text', $input->val());
        $this->assertEquals('text', $input->attr('type'));

        $label = $pq->find('label');
        $this->assertTrue($label->length == 1);
        $this->assertEquals('label text', $label->find('span')->text());
    }

    /**
     * check that posted values overwrite preset default
     */
    function test_prefill() {
        global $INPUT;
        $INPUT->post->set('foo', 'a new text');

        $form = new Form\Form();
        $form->addTextInput('foo', 'label text')->val('this is text');

        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $input = $pq->find('input[name=foo]');
        $this->assertTrue($input->length == 1);
        $this->assertEquals('a new text', $input->val());
    }

    function test_prefill_empty() {
        global $INPUT;
        $INPUT->post->set('foo', '');

        $form = new Form\Form();
        $form->addTextInput('foo', 'label text')->val('this is text');

        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $input = $pq->find('input[name=foo]');
        $this->assertTrue($input->length == 1);
        $this->assertEquals('', $input->val());
    }


    function test_password() {
        $form = new Form\Form();
        $form->addPasswordInput('foo', 'label text')->val('this is text');

        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $input = $pq->find('input[name=foo]');
        $this->assertTrue($input->length == 1);
        $this->assertEquals('this is text', $input->val());
        $this->assertEquals('password', $input->attr('type'));

        $label = $pq->find('label');
        $this->assertTrue($label->length == 1);
        $this->assertEquals('label text', $label->find('span')->text());
    }
}
