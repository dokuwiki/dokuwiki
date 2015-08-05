<?php

class general_languagelint_test extends DokuWikiTest {


    function test_core() {
        $this->checkFiles(glob(DOKU_INC.'inc/lang/*/*.php'));
    }

    function test_plugins() {
        $this->checkFiles(glob(DOKU_INC.'lib/plugins/*/lang/*/*.php'));
    }

    /**
     * Run checks over the given PHP language files
     *
     * @param $files
     */
    private function checkFiles($files){
        foreach($files as $file){
            // try to load the file
            include $file;
            // check it defines an array
            $this->assertTrue(is_array($lang), $file);
            unset($lang);

            $this->checkUgly($file);
        }
    }

    /**
     * Checks if the file contains any ugly things like leading whitespace, BOM or trailing
     * PHP closing mark
     *
     * @param $file
     * @throws Exception
     */
    private function checkUgly($file){
        $content = rtrim(file_get_contents($file));
        if(substr($content,0,5) != '<?php')
            throw new Exception("$file does not start with '<?php' - check for BOM");

        if(substr($content,-2) == '?>')
            throw new Exception("$file ends with '?>' - remove it!");
    }

}
