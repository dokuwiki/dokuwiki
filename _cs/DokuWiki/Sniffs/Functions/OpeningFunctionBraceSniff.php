<?php
/**
 * Generic_Sniffs_Functions_OpeningFunctionBraceKernighanRitchieSniff.
 */

class DokuWiki_Sniffs_Functions_OpeningFunctionBraceSniff implements PHP_CodeSniffer_Sniff {


    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return void
     */
    public function register()
    {
        return array(T_FUNCTION);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            return;
        }

        $openingBrace = $tokens[$stackPtr]['scope_opener'];

        // The end of the function occurs at the end of the argument list. Its
        // like this because some people like to break long function declarations
        // over multiple lines.
        $functionLine = $tokens[$tokens[$stackPtr]['parenthesis_closer']]['line'];
        $braceLine    = $tokens[$openingBrace]['line'];

        $lineDifference = ($braceLine - $functionLine);

        if ($lineDifference > 0) {
            $error = 'Opening brace should be on the same line as the declaration';
            $phpcsFile->addError($error, $openingBrace);
            return;
        }

        // Checks that the closing parenthesis and the opening brace are
        // separated by a whitespace character.
        $closerColumn = $tokens[$tokens[$stackPtr]['parenthesis_closer']]['column'];
        $braceColumn  = $tokens[$openingBrace]['column'];

        $columnDifference = ($braceColumn - $closerColumn);

        if ($columnDifference > 2) {
            $error = 'Expected 0 or 1 space between the closing parenthesis and the opening brace; found '.($columnDifference - 1).'.';
            $phpcsFile->addError($error, $openingBrace);
            return;
        }

        // Check that a tab was not used instead of a space.
        $spaceTokenPtr = ($tokens[$stackPtr]['parenthesis_closer'] + 1);
        $spaceContent  = $tokens[$spaceTokenPtr]['content'];
        if ($columnDifference == 2 && $spaceContent !== ' ') {
            $error = 'Expected a none or a single space character between closing parenthesis and opening brace; found "'.$spaceContent.'".';
            $phpcsFile->addError($error, $openingBrace);
            return;
        }

    }//end process()


}//end class

?>
