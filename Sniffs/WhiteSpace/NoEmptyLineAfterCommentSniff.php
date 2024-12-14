<?php

namespace EpiphytCodingStandard\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class NoEmptyLineAfterCommentSniff implements Sniff
{
    public function register()
    {
        return [
            T_COMMENT,
            T_DOC_COMMENT_CLOSE_TAG,
        ];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

        // Check if the next token is on the same line
        if (($tokens[$nextToken]['line'] - $tokens[$stackPtr]['line']) > 1) {
            // Check if previous token is on the same line
            if ($tokens[$stackPtr]['line'] !== $tokens[$prevToken]['line']) {
                if ($tokens[$nextToken]['code'] !== T_DOC_COMMENT_OPEN_TAG) {
                    $phpcsFile->addError('An empty line after a comment is not allowed', $stackPtr, 'NoEmptyLineAfterComment');
                }
            }
        }
    }
}
