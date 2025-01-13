<?php
/**
 * Verifies that there are no else if statements (elseif should be used instead).
 */

namespace HeroRules\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ElseIfDeclarationSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array<int|string>
     */
    public function register()
    {
        return [T_ELSEIF];
    }


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $currentLineTokens = $this->getTokensByLine($tokens, $tokens[$stackPtr]['line']);
        $lasControlStructureToken = $currentLineTokens[(\count($currentLineTokens) - 2)];

        if ($lasControlStructureToken['code'] === T_COLON) {
            return;
        }

        $error = 'Usage of "elseif" is not allowed; use "else if" instead.';
        $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'NotAllowed');

        if ($fix === true) {
            $phpcsFile->fixer->replaceToken($stackPtr, 'else if');
        }
    }
    
    /**
     * Gets all tokens on a specific line.
     *
     * @param array $tokens All the tokens.
     * @param int  $line      The line number to retrieve tokens for.
     *
     * @return array The tokens on the specified line.
     */
    private function getTokensByLine(array $tokens, int $line): array
    {
        $lineTokens = [];

        foreach ($tokens as $token) {
            if ($token['line'] === $line) {
                $lineTokens[] = $token;
            }
            else if ($token['line'] > $line) {
                return $lineTokens;
            }
        }

        return $lineTokens;
    }
}
