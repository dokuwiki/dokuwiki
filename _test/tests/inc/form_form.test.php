<?php

class form_test extends DokuWikiTest {

  function _testform() {
    $form = new Doku_Form(array('id' => 'dw__testform', 'action' => '/test'));
    $form->startFieldset('Test');
    $form->addHidden('summary', 'changes &c');
    $form->addElement(form_makeTextField('t', 'v', 'Text', 'text__id', 'block'));
    $form->addElement(form_makeCheckboxField('r', '1', 'Check', 'check__id', 'simple'));
    $form->addElement(form_makeButton('submit', 'save', 'Save', array('accesskey'=>'s')));
    $form->addElement(form_makeButton('submit', 'cancel', 'Cancel'));
    $form->endFieldset();
    return $form;
  }

  function _realoutput() {
    global $lang;
    $realoutput  = '<form id="dw__testform" action="/test" method="post" ';
    $realoutput .= 'accept-charset="'.$lang['encoding'].'">';
    $realoutput .= "\n";
    $realoutput .= '<div class="no"><input type="hidden" name="sectok" value="'.getSecurityToken().'" />';
    $realoutput .= '<input type="hidden" name="summary" value="changes &amp;c" />';
    $realoutput .= "\n";
    $realoutput .= "<fieldset ><legend>Test</legend>\n";
    $realoutput .= '<label class="block" for="text__id"><span>Text</span> ';
    $realoutput .= '<input type="text" id="text__id" name="t" value="v" class="edit" /></label><br />';
    $realoutput .= "\n";
    $realoutput .= '<label class="simple" for="check__id">';
    $realoutput .= '<input type="checkbox" id="check__id" name="r" value="1" /> ';
    $realoutput .= '<span>Check</span></label>';
    $realoutput .= "\n";
    $realoutput .= '<input name="do[save]" type="submit" value="Save" class="button" accesskey="s" title="Save [S]" />';
    $realoutput .= "\n";
    $realoutput .= '<input name="do[cancel]" type="submit" value="Cancel" class="button" />';
    $realoutput .= "\n";
    $realoutput .= "</fieldset>\n</div></form>\n";
    return $realoutput;
  }

  function _ignoreTagWS($data){
    return preg_replace('/>\s+</','><',$data);
  }

  function test_form_print() {
    $form = $this->_testform();
    ob_start();
    $form->printForm();
    $output = ob_get_contents();
    ob_end_clean();
    $form->addHidden('sectok', getSecurityToken());
    $this->assertEquals($this->_ignoreTagWS($output),$this->_ignoreTagWS($this->_realoutput()));
  }

  function test_get_element_at() {
    $form = $this->_testform();
    $e1 =& $form->getElementAt(1);
    $this->assertEquals($e1, array('_elem'=>'textfield',
                                 '_text'=>'Text',
                                 '_class'=>'block',
                                 'id'=>'text__id',
                                 'name'=>'t',
                                 'value'=>'v',
                                 'class'=>'edit'));
    $e2 =& $form->getElementAt(99);
    $this->assertEquals($e2, array('_elem'=>'closefieldset'));
  }

  function test_find_element_by_type() {
    $form = $this->_testform();
    $this->assertEquals($form->findElementByType('button'), 3);
    $this->assertFalse($form->findElementByType('text'));
  }

  function test_find_element_by_id() {
    $form = $this->_testform();
    $this->assertEquals($form->findElementById('check__id'), 2);
    $this->assertFalse($form->findElementById('dw__testform'));
  }

  function test_find_element_by_attribute() {
    $form = $this->_testform();
    $this->assertEquals($form->findElementByAttribute('value','Cancel'), 4);
    $this->assertFalse($form->findElementByAttribute('name','cancel'));
  }

  function test_close_fieldset() {
    $form = new Doku_Form(array('id' => 'dw__testform', 'action' => '/test'));
    $form->startFieldset('Test');
    $form->addHidden('summary', 'changes &c');
    $form->addElement(form_makeTextField('t', 'v', 'Text', 'text__id', 'block'));
    $form->addElement(form_makeCheckboxField('r', '1', 'Check', 'check__id', 'simple'));
    $form->addElement(form_makeButton('submit', 'save', 'Save', array('accesskey'=>'s')));
    $form->addElement(form_makeButton('submit', 'cancel', 'Cancel'));
    ob_start();
    $form->printForm();
    $output = ob_get_contents();
    ob_end_clean();
    $this->assertEquals($this->_ignoreTagWS($output),$this->_ignoreTagWS($this->_realoutput()));
  }

}
