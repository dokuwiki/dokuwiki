<?php
if (!defined('DOKU_BASE')) define('DOKU_BASE','./');

require_once 'parser.inc.php';
require_once DOKU_INC.'inc/parser/xhtml.php';
require_once DOKU_INC.'inc/geshi.php';

if (!extension_loaded('runkit')) {
    SimpleTestOptions::ignore('xhtml_htmlphp_test');
    trigger_error('Skipping xhtml_htmlphp_test - http://www.php.net/runkit required');
}

function xhtml_htmlphp_test_io_makefiledir() {
  return;
}
function xhtml_htmlphp_test_io_savefile() {
  return true;
}


class Doku_Renderer_tester extends Doku_Renderer_xhtml {

/*
   changes to these tests remove the need to redefine any xhtml methods
   class left for future use
 */

}

/*
 * test case for parser/xhtml.php _headertolink method
 * definition:   function _headertolink($title,$create)
 */

class xhtml_htmlphp_test extends  TestOfDoku_Parser {

    var $purge;
    var $cachedir;

    function setup() {
      global $conf;

      // set purge to avoid trying to retrieve from cache
      $this->purge = isset($_REQUEST['purge']) ? $_REQUEST['purge'] : null;
      $_REQUEST['purge'] = 1;

      if (!isset($conf['cachedir'])) {
        $conf['cachedir'] = '';
        $this->cachedir = false;
      } else {
        $this->cachedir = true;
      }

      if (function_exists('io_makefiledir')) {
        runkit_function_rename('io_makefiledir', 'io_makefiledir_real');
      }
      runkit_function_rename('xhtml_htmlphp_test_io_makefiledir','io_makefiledir');

      if (function_exists('io_savefile')) {
        runkit_function_rename('io_savefile', 'io_savefile_real');
      }
      runkit_function_rename('xhtml_htmlphp_test_io_savefile','io_savefile');

      runkit_method_rename('GeSHi','parse_code','parse_code_real');
      runkit_method_add('GeSHi','parse_code','', '{ return hsc($this->source); }');

      parent::setup();
    }

    function teardown() {
      global $conf;

      // restore purge
      if (is_null($this->purge)) unset($_REQUEST['purge']);
      else $_REQUEST['purge'] = $this->purge;

      // restore $conf['cachedir'] if necessary
      if (!$this->cachedir) unset($conf['cachedir']);

      // restore io_functions
      runkit_function_rename('io_makefiledir','xhtml_htmlphp_test_io_makefiledir');
      if (function_exists('io_makefiledir_real')) {
        runkit_function_rename('io_makefiledir_real', 'io_makefiledir');
      }

      runkit_function_rename('io_savefile','xhtml_htmlphp_test_io_savefile');
      if (function_exists('io_savefile_real')) {
        runkit_function_rename('io_savefile_real', 'io_savefile');
      }

      // restore GeSHi::parse_code
      runkit_method_remove('GeSHi','parse_code');
      runkit_method_rename('GeSHi','parse_code_real','parse_code');

      parent::setup();
    }

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
        $test   = array('<html><b>bold</b></html>','<p><code class="code html4strict">&lt;b&gt;bold&lt;/b&gt;</code></p>');

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
        $test   = array('<HTML><b>bold</b></HTML>','<pre class="code html4strict">&lt;b&gt;bold&lt;/b&gt;</pre>');

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
        $test   = array('<php>echo(1+1);</php>','<p><code class="code php">echo(1+1);</code></p>');

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
        $test   = array('<PHP>echo(1+1);</PHP>','<pre class="code php">echo(1+1);</pre>');

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
