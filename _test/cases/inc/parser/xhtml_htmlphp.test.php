<?php
if (!defined('DOKU_BASE')) define('DOKU_BASE','./');
require_once 'parser.inc.php';
require_once DOKU_INC.'inc/parser/xhtml.php';

class Doku_Renderer_tester extends Doku_Renderer_xhtml {

   // simplify to avoid GeSHi
   function code($text, $language = NULL) {
     $this->preformatted($text);
   }

}

/*
 * test case for parser/xhtml.php _headertolink method
 * definition:   function _headertolink($title,$create)
 */

class xhtml_htmlphp_test extends  TestOfDoku_Parser {

    function _run_parser($modes,$data) {
    
      foreach ($modes as $mode => $name) {
        $class = 'Doku_Parser_Mode_'.$name;
        $this->P->addMode($mode,new $class());
      }
      
      $R = new Doku_Renderer_tester();
      $this->P->parse($data);
      foreach ( $this->H->calls as $instruction ) {
        // Execute the callback against the Renderer
        call_user_func_array(array(&$R, $instruction[0]),$instruction[1]);
      }
      
      return str_replace("\n",'',$R->doc);
    }

    function test_html_off(){
        $test   = array('<html><b>bold</b></html>','<p><pre class="code">&lt;b&gt;bold&lt;/b&gt;</pre></p>');

        global $conf;
        $conf['htmlok'] = 0;

        $result = $this->_run_parser(array('html'=>'html'),$test[0]);

        $this->assertEqual($result,$test[1]);
    }

    function test_html_on(){
        $test   = array('<html><b>bold</b></html>','<p><b>bold</b></p>');

        global $conf;
        $conf['htmlok'] = 1;

        $result = $this->_run_parser(array('html'=>'html'),$test[0]);

        $this->assertEqual($result,$test[1]);
    }

    function test_htmlblock_off(){
        $test   = array('<HTML><b>bold</b></HTML>','<pre class="code">&lt;b&gt;bold&lt;/b&gt;</pre>');

        global $conf;
        $conf['htmlok'] = 0;

        $result = $this->_run_parser(array('html'=>'html'),$test[0]);

        $this->assertEqual($result,$test[1]);
    }

    function test_htmlblock_on(){
        $test   = array('<HTML><b>bold</b></HTML>','<b>bold</b>');

        global $conf;
        $conf['htmlok'] = 1;

        $result = $this->_run_parser(array('html'=>'html'),$test[0]);

        $this->assertEqual($result,$test[1]);
    }

    function test_php_off(){
        $test   = array('<php>echo(1+1);</php>','<p><pre class="code">echo(1+1);</pre></p>');

        global $conf;
        $conf['phpok'] = 0;

        $result = $this->_run_parser(array('php'=>'php'),$test[0]);

        $this->assertEqual($result,$test[1]);
    }

    function test_php_on(){
        $test   = array('<php>echo(1+1);</php>','<p>2</p>');

        global $conf;
        $conf['phpok'] = 1;

        $result = $this->_run_parser(array('php'=>'php'),$test[0]);

        $this->assertEqual($result,$test[1]);
    }

    function test_phpblock_off(){
        $test   = array('<PHP>echo(1+1);</PHP>','<pre class="code">echo(1+1);</pre>');

        global $conf;
        $conf['phpok'] = 0;

        $result = $this->_run_parser(array('php'=>'php'),$test[0]);

        $this->assertEqual($result,$test[1]);
    }

    function test_phpblock_on(){
        $test   = array('<PHP>echo(1+1);</PHP>',"2");

        global $conf;
        $conf['phpok'] = 1;

        $result = $this->_run_parser(array('php'=>'php'),$test[0]);

        $this->assertEqual($result,$test[1]);
    }

}
