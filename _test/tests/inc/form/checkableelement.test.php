<?php

use dokuwiki\Form;

class form_checkableelement_test extends DokuWikiTest {

    function test_defaults() {
        $form = new Form\Form();
        $form->addRadioButton('foo', 'label text first')->val('first')->attr('checked', 'checked');
        $form->addRadioButton('foo', 'label text second')->val('second');

        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $input = $pq->find('input[name=foo]');
        $this->assertTrue($input->length == 2);

        $label = $pq->find('label');
        $this->assertTrue($label->length == 2);

        $inputs = $pq->find('input[name=foo]');
        $this->assertEquals('first', pq($inputs->elements[0])->val());
        $this->assertEquals('second', pq($inputs->elements[1])->val());
        $this->assertEquals('checked', pq($inputs->elements[0])->attr('checked'));
        $this->assertEquals('', pq($inputs->elements[1])->attr('checked'));
    }

    /**
     * check that posted values overwrite preset default
     */
    function test_prefill() {
        global $INPUT;
        $INPUT->post->set('foo', 'second');


        $form = new Form\Form();
        $form->addRadioButton('foo', 'label text first')->val('first')->attr('checked', 'checked');
        $form->addRadioButton('foo', 'label text second')->val('second');

        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $inputs = $pq->find('input[name=foo]');
        $this->assertEquals('first', pq($inputs->elements[0])->val());
        $this->assertEquals('second', pq($inputs->elements[1])->val());
        $this->assertEquals('', pq($inputs->elements[0])->attr('checked'));
        $this->assertEquals('checked', pq($inputs->elements[1])->attr('checked'));
    }
}
