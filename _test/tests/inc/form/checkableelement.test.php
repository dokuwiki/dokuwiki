<?php

use dokuwiki\Form;
use DOMWrap\Document;

class form_checkableelement_test extends DokuWikiTest {

    function test_defaults() {
        $form = new Form\Form();
        $form->addRadioButton('foo', 'label text first')->val('first')->attr('checked', 'checked');
        $form->addRadioButton('foo', 'label text second')->val('second');

        $html = $form->toHTML();
        $pq = (new Document())->html($html);

        $input = $pq->find('input[name=foo]');
        $this->assertTrue($input->count() == 2);

        $label = $pq->find('label');
        $this->assertTrue($label->count() == 2);

        $inputs = $pq->find('input[name=foo]');
        $this->assertEquals('first', $inputs->get(0)->attr('value'));
        $this->assertEquals('second', $inputs->get(1)->attr('value'));
        $this->assertEquals('checked', $inputs->get(0)->attr('checked'));
        $this->assertEquals('', $inputs->get(1)->attr('checked'));
        $this->assertEquals('radio', $inputs->get(0)->attr('type'));
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
        $pq = (new Document())->html($html);

        $inputs = $pq->find('input[name=foo]');
        $this->assertEquals('first', $inputs->get(0)->attr('value'));
        $this->assertEquals('second', $inputs->get(1)->attr('value'));
        $this->assertEquals('', $inputs->get(0)->attr('checked'));
        $this->assertEquals('checked', $inputs->get(1)->attr('checked'));
        $this->assertEquals('radio', $inputs->get(0)->attr('type'));
    }
}
