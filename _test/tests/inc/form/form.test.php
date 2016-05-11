<?php

use dokuwiki\Form;

/**
 * makes form internals accessible for testing
 */
class TestForm extends Form\Form {
    /**
     * @return array list of element types
     */
    function getElementTypeList() {
        $list = array();
        foreach($this->elements as $element) $list[] = $element->getType();
        return $list;
    }

    public function balanceFieldsets() {
        parent::balanceFieldsets();
    }

}

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


    function test_fieldsetbalance() {
        $form = new TestForm();
        $form->addFieldsetOpen();
        $form->addHTML('ignored');
        $form->addFieldsetClose();
        $form->balanceFieldsets();

        $this->assertEquals(
            array(
                'fieldsetopen',
                'html',
                'fieldsetclose'
            ),
            $form->getElementTypeList()
        );

        $form = new TestForm();
        $form->addHTML('ignored');
        $form->addFieldsetClose();
        $form->balanceFieldsets();

        $this->assertEquals(
            array(
                'fieldsetopen',
                'html',
                'fieldsetclose'
            ),
            $form->getElementTypeList()
        );


        $form = new TestForm();
        $form->addFieldsetOpen();
        $form->addHTML('ignored');
        $form->balanceFieldsets();

        $this->assertEquals(
            array(
                'fieldsetopen',
                'html',
                'fieldsetclose'
            ),
            $form->getElementTypeList()
        );

        $form = new TestForm();
        $form->addHTML('ignored');
        $form->addFieldsetClose();
        $form->addHTML('ignored');
        $form->addFieldsetOpen();
        $form->addHTML('ignored');
        $form->balanceFieldsets();

        $this->assertEquals(
            array(
                'fieldsetopen',
                'html',
                'fieldsetclose',
                'html',
                'fieldsetopen',
                'html',
                'fieldsetclose'
            ),
            $form->getElementTypeList()
        );
    }

}
