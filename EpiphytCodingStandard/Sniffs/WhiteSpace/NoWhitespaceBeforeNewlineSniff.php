<?php

namespace EpiphytCodingStandard\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class NoWhitespaceBeforeNewlineSniff implements Sniff
{
    public function register()
    {
        return [T_WHITESPACE];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        $previousToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

        if ($tokens[$previousToken]['line'] === $tokens[$stackPtr]['line'] && preg_match('/\s\n$/', $tokens[$stackPtr]['content']) && $tokens[$nextToken]['line'] > $tokens[$stackPtr]['line']) {
            $fix = $phpcsFile->addFixableError('A whitespace is not allowed at the end of the line', $stackPtr, 'NoWhitespaceBeforeNewline');

            if ($fix) {
                // Remove the spaces or tabs before the newline
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->replaceToken($stackPtr, '');
                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}
