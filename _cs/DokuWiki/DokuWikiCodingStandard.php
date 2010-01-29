<?php
/**
 * DokuWiki Coding Standard.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Andreas Gohr <andi@splitbrain.org>
 */

if (class_exists('PHP_CodeSniffer_Standards_CodingStandard', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_CodingStandard not found');
}

/**
 * DokuWiki Coding Standard.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Andreas Gohr <andi@splitbrain.org>
 */
class PHP_CodeSniffer_Standards_DokuWiki_DokuWikiCodingStandard extends PHP_CodeSniffer_Standards_CodingStandard {


    /**
     * Return a list of external sniffs to include with this standard.
     *
     * @return array
     */
    public function getIncludedSniffs() {
        return array(
            'Generic/Sniffs/Classes/DuplicateClassNameSniff.php',
            'Generic/Sniffs/CodeAnalysis/JumbledIncrementerSniff.php',
            'Generic/Sniffs/CodeAnalysis/UnnecessaryFinalModifierSniff.php',
            'Generic/Sniffs/CodeAnalysis/UnconditionalIfStatementSniff.php',
            'Generic/Sniffs/CodeAnalysis/ForLoopShouldBeWhileLoopSniff.php',
            'Generic/Sniffs/CodeAnalysis/ForLoopWithTestFunctionCallSniff.php',
            'Generic/Sniffs/CodeAnalysis/UnusedFunctionParameterSniff.php',
            'Generic/Sniffs/CodeAnalysis/EmptyStatementSniff.php',
            'Generic/Sniffs/CodeAnalysis/UselessOverridingMethodSniff.php',
            'Generic/Sniffs/Commenting/TodoSniff.php',
            'Generic/Sniffs/Files/LineEndingsSniff.php',
            'Generic/Sniffs/Formatting/DisallowMultipleStatementsSniff.php',
            'Generic/Sniffs/Metrics/NestingLevelSniff.php',
//            'Generic/Sniffs/Metrics/CyclomaticComplexitySniff.php', //FIXME we might need to tune this first
            'Generic/Sniffs/NamingConventions/UpperCaseConstantNameSniff.php',
            'Generic/Sniffs/PHP/LowerCaseConstantSniff.php',
            'Generic/Sniffs/PHP/DisallowShortOpenTagSniff.php',
            'Generic/Sniffs/PHP/ForbiddenFunctionsSniff.php',
            'Generic/Sniffs/WhiteSpace/DisallowTabIndentSniff.php',
            'DokuWiki/Sniffs/WhiteSpace/ScopeIndentSniff.php',
            'Zend/Sniffs/Files/ClosingTagSniff.php',
            'PEAR/Sniffs/Functions/ValidDefaultValueSniff.php',
            'Squiz/Sniffs/PHP/EvalSniff.php',
            'Squiz/Sniffs/PHP/NonExecutableCodeSniff.php',
//            'Squiz/Sniffs/PHP/CommentedOutCodeSniff.php', //FIXME should ignore oneliners
            'Squiz/Sniffs/WhiteSpace/SuperfluousWhitespaceSniff.php',
            'Squiz/Sniffs/PHP/NonExecutableCodeSniff.php',
            'Squiz/Sniffs/CSS/LowercaseStyleDefinitionSniff.php',
            'Squiz/Sniffs/CSS/MissingColonSniff.php',
            'Squiz/Sniffs/CSS/DisallowMultipleStyleDefinitionsSniff.php',
            'Squiz/Sniffs/CSS/ColonSpacingSniff.php',
            'Squiz/Sniffs/CSS/ClassDefinitionClosingBraceSpaceSniff.php',
            'Squiz/Sniffs/CSS/SemicolonSpacingSniff.php',
            'Squiz/Sniffs/CSS/IndentationSniff.php',
            'Squiz/Sniffs/CSS/EmptyClassDefinitionSniff.php',
            'Squiz/Sniffs/CSS/ClassDefinitionNameSpacingSniff.php',
            'Squiz/Sniffs/CSS/EmptyStyleDefinitionSniff.php',
            'Squiz/Sniffs/CSS/OpacitySniff.php',
            'Squiz/Sniffs/CSS/ColourDefinitionSniff.php',
            'Squiz/Sniffs/CSS/DuplicateClassDefinitionSniff.php',
            'Squiz/Sniffs/CSS/ClassDefinitionOpeningBraceSpaceSniff.php',

            'Squiz/Sniffs/Commenting/DocCommentAlignmentSniff.php',

        );
    }

}//end class
