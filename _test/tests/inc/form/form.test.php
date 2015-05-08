<?php

use dokuwiki\Form;

class form_form_test extends DokuWikiTest {

    /**
     * checks that an empty form is initialized correctly
     */
    function test_defaults() {
        global $INPUT;
        global $ID;
        $ID = 'some:test';
        $INPUT->get->set('id', $ID);
        $INPUT->get->set('foo', 'bar');

        $form = new Form\Form();
        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $this->assertTrue($pq->find('form')->hasClass('doku_form'));
        $this->assertEquals(wl($ID, array('foo' => 'bar'), false, '&'), $pq->find('form')->attr('action'));
        $this->assertEquals('post', $pq->find('form')->attr('method'));

        $this->assertTrue($pq->find('input[name=sectok]')->length == 1);
    }

}
