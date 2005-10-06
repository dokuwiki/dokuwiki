<?php
/**
* @package WACT_TESTS
* @version $Id: web.inc.php,v 1.6 2005/08/20 09:46:06 pachanga Exp $
*/

SimpleTestOptions::ignore('DWWebTestCase');

class DWWebTestCase extends WebTestCase {

    function assertNormalPage() {
        $this->assertResponse(array(200));
        $this->assertNoUnwantedPattern('/Warning:/i');
        $this->assertNoUnwantedPattern('/Error:/i');
        $this->assertNoUnwantedPattern('/Fatal error/i');
    }

    function assertWantedLiteral($str) {
        $this->assertWantedPattern('/' . preg_quote($str, '/').  '/');
    }

    function assertNoUnWantedLiteral($str) {
        $this->assertNoUnWantedPattern('/' . preg_quote($str, '/').  '/');
    }

    function &_fileToPattern($file) {
        $file_as_array = file($file);
        $pattern = '#^';
        foreach ($file_as_array as $line) {
            /* strip trailing newline */
            if ($line[strlen($line) - 1] == "\n") {
                $line = substr($line, 0, strlen($line) - 1);
            }
            $line = preg_quote($line, '#');

            /* replace paths with wildcard */
            $line = preg_replace("#'/[^']*#", "'.*", $line);

            $pattern .= $line . '\n';
        }
        /* strip final newline */
        $pattern = substr($pattern, 0, strlen($pattern) - 2);
        $pattern .= '$#i';
        return $pattern;
    }

}
?>
