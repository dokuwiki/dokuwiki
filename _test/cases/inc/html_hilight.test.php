<?php

require_once DOKU_INC.'inc/html.php';

if (!extension_loaded('runkit')) {
    SimpleTestOptions::ignore('html_hilight_test');
    trigger_error('Skipping html_hilight_test - http://www.php.net/runkit required');
}

function html_hilight_test_unslash($string,$char="'"){
  $str= str_replace('\\'.$char,$char,$string);
  return $str;
}

class html_hilight_test extends UnitTestCase{

  function setup() {
    if ( function_exists('unslash') ) {
        runkit_function_rename('unslash','html_hilight_test_unslash_real');
    }
    runkit_function_rename('html_hilight_test_unslash','unslash');
  }

  function teardown() {
    runkit_function_rename('unslash','html_hilight_test_unslash');
    if ( function_exists('html_hilight_test_unslash_real') ) {
        runkit_function_rename('html_hilight_test_unslash_real','unslash');
    }
  }

  function testHighlightOneWord() {
    $html = 'Foo bar Foo';
    $this->assertPattern(
      '/Foo <span.*>bar<\/span> Foo/',
      html_hilight($html,'bar')
      );
  }

  function testHighlightTwoWords() {
    $html = 'Foo bar Foo php Foo';
    $this->assertPattern(
      '/Foo <span.*>bar<\/span> Foo <span.*>php<\/span> Foo/',
      html_hilight($html,array('bar','php'))
      );
  }

  function testHighlightTwoWordsHtml() {
    $html = 'Foo <b>bar</b> <i>Foo</i> php Foo';
    $this->assertPattern(
      '/Foo <b><span.*>bar<\/span><\/b> <i>Foo<\/i> <span.*>php<\/span> Foo/',
      html_hilight($html,array('bar','php'))
      );
  }

  function testNoHighlight() {
    $html = 'Foo bar Foo';
    $this->assertPattern(
      '/Foo bar Foo/',
      html_hilight($html,'php')
      );
  }

  function testHighlightPHP() {
    $html = 'Foo $_GET[\'bar\'] Foo';
    $this->assertEqual(
      'Foo <span class="search_hit">$_GET[\'bar\']</span> Foo',
      html_hilight($html,'$_GET[\'bar\']')
      );
  }

  function testMatchAttribute() {
    $html = 'Foo <b class="x">bar</b> Foo';
    $this->assertPattern(
      '/Foo <b class="x">bar<\/b> Foo/',
      html_hilight($html,'class="x"')
      );
  }

  function testMatchAttributeWord() {
    $html = 'Foo <b class="x">bar</b> Foo';
    $this->assertEqual(
      'Foo <b class="x">bar</b> Foo',
      html_hilight($html,'class="x">bar')
      );
  }

  function testRegexInjection() {
    $html = 'Foo bar Foo';
    $this->assertPattern(
      '/Foo bar Foo/',
      html_hilight($html,'*')
      );
  }

  function testRegexInjectionSlash() {
    $html = 'Foo bar Foo';
    $this->assertPattern(
      '/Foo bar Foo/',
      html_hilight($html,'x/')
      );
  }

}

