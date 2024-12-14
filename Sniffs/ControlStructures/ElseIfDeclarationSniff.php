<?php

namespace EpiphytCodingStandard\Sniffs\ControlStructures;

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
        $error = 'Usage of "elseif" is not allowed; use "else if" instead.';
        $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'NotAllowed');

        if ($fix === true) {
            $phpcsFile->fixer->replaceToken($stackPtr, 'else if');
        }
    }
}
