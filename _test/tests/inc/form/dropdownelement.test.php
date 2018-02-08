<?php

use dokuwiki\Form;

class form_dropdownelement_test extends DokuWikiTest {

    function test_defaults() {
        $form = new Form\Form();

        // basic tests
        $options = array ('first', 'second', 'third');
        $element = $form->addDropdown('foo', $options, 'label text');
        $this->assertEquals('first', $element->val());
        $element->val('second');
        $this->assertEquals('second', $element->val());
        $element->val('nope');
        $this->assertEquals('first', $element->val());

        // associative array
        $options = array ('first'=>'A first Label', 'second'=>'The second Label', 'third'=>'Just 3');
        $element->options($options);
        $this->assertEquals('first', $element->val());
        $element->val('second');
        $this->assertEquals('second', $element->val());
        $element->val('nope');
        $this->assertEquals('first', $element->val());

        // HTML
        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $select = $pq->find('select[name=foo]');
        $this->assertTrue($select->length == 1);
        $this->assertEquals('first', $select->val());

        $options = $pq->find('option');
        $this->assertTrue($options->length == 3);

        $option = $pq->find('option[selected=selected]');
        $this->assertTrue($option->length == 1);
        $this->assertEquals('first', $option->val());
        $this->assertEquals('A first Label', $option->text());

        $label = $pq->find('label');
        $this->assertTrue($label->length == 1);
        $this->assertEquals('label text', $label->find('span')->text());
    }

    function test_extended_options() {
        $form = new Form\Form();

        $options = array(
            'first' => array (
                'label' => 'the label',
                'attrs' => array(
                    'id' => 'theID',
                    'class' => 'two classes',
                    'data-foo' => 'bar'
                )
            ),
            'second',
            '3' => array(
                'label' => 'the label of the complex third option',
            )
        );

        $form->addDropdown('foo', $options, 'label text');
        // HTML
        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $select = $pq->find('select[name=foo]');
        $this->assertTrue($select->length == 1);

        $options = $pq->find('option');
        $this->assertEquals(3, $options->length);

        $option = $pq->find('option#theID');
        $this->assertEquals(1, $option->length);
        $this->assertEquals('first', $option->val());
        $this->assertEquals('the label', $option->text());
        $this->assertEquals('bar', $option->attr('data-foo'));
        $this->assertTrue($option->hasClass('two'));
        $this->assertTrue($option->hasClass('classes'));
    }

    public function test_optgroups() {
        $form = new Form\Form();

        $options1 = array(
            'first' => 'the label',
            'second'
        );

        $options2 = array(
            'third' => array (
                'label' => 'label of third option',
                'attribute' => 'attribute-value'
            ),
            'fourth'
        );

        $dropdown = $form->addDropdown('foo', null, 'label text');
        $dropdown->addOptGroup('opt1', $options1);
        $dropdown->addOptGroup('opt2', $options2);

        $dropdown->val('third');
        $this->assertEquals('third', $dropdown->val());

        /** @var Form\OptGroup[] $optGroups */
        $optGroups = $dropdown->optGroups();
        $this->assertEquals(array(
            'first' => array('label' => 'the label'),
            'second' => array('label' => 'second')
        ), $optGroups['opt1']->options());

        // HTML
        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $optGroupsHTML = $pq->find('optgroup');
        $this->assertEquals(2, $optGroupsHTML->length);

        $options = $pq->find('option');
        $this->assertEquals(4, $options->length);

        $selected = $pq->find('option[selected=selected]');
        $this->assertEquals('third', $selected->val());
        $this->assertEquals('label of third option', $selected->text());
    }

    /**
     * Prevent double select that might occur because `'Auto' == 0` is true
     */
    public function test_doubleselect() {
        $form = new Form\Form();
        $form->addDropdown('foo', ['Auto', 0, 1]);

        $html = $form->toHTML();

        $pq = phpQuery::newDocumentXHTML($html);
        $selected = $pq->find('option[selected=selected]');
        $this->assertEquals(1, $selected->length);
        $this->assertEquals('Auto', $selected->text());
    }

    /**
     * Ensure that there is always only a single one selected option
     */
    public function test_optgroups_doubleselect() {
        $form = new Form\Form();
        $options1 = array(
            'double' => 'the label'
        );

        $options2 = array(
            'double' => array (
                'label' => 'label of third option',
                'attribute' => 'attribute-value'
            )
        );

        $dropdown = $form->addDropdown('foo', null, 'label text');
        $dropdown->addOptGroup('opt1', $options1);
        $dropdown->addOptGroup('opt2', $options2);
        $dropdown->val('double');

        // HTML
        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);
        $selected = $pq->find('option[selected=selected]');
        $this->assertEquals(1, $selected->length);
        $this->assertEquals('the label', $selected->text());
    }


    /**
     * check that posted values overwrite preset default
     */
    function test_prefill() {
        global $INPUT;
        $INPUT->post->set('foo', 'second');

        $form = new Form\Form();
        $options = array ('first'=>'A first Label', 'second'=>'The second Label', 'third'=>'Just 3');
        $element = $form->addDropdown('foo', $options, 'label text')->val('third');
        $this->assertEquals('third', $element->val());

        $html = $form->toHTML();
        $pq = phpQuery::newDocumentXHTML($html);

        $option = $pq->find('option[selected=selected]');
        $this->assertTrue($option->length == 1);
        $this->assertEquals('second', $option->val());
        $this->assertEquals('The second Label', $option->text());
    }
}
