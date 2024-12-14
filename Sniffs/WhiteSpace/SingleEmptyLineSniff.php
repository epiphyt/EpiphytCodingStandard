<?php

namespace EpiphytCodingStandard\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class SingleEmptyLineSniff implements Sniff
{
    public function register()
    {
        return [T_WHITESPACE];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $nextToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

        if (empty(trim($tokens[$stackPtr]['content'])) && ($tokens[$stackPtr]['line'] - $tokens[$nextToken]['line']) > 2) {
            $fix = $phpcsFile->addFixableError('Only a single empty line is allowed', $stackPtr, 'NoWhitespaceAtLineEnd');

            if ($fix) {
                // Remove the trailing whitespace
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($stackPtr, '');
                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}
