<?php

namespace EpiphytCodingStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class SpaceAfterAsteriskSniff implements Sniff
{
    public function register()
    {
        return [ T_DOC_COMMENT_STAR ];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Check if there's a space after the asterisk
        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ( $stackPtr + 1 ), null, true);
        if (!\str_starts_with($tokens[$nextToken]['content'], ' ')) {
            $fix = $phpcsFile->addFixableError('Space required after asterisk in docblock comment', $stackPtr, 'NoSpaceAfterAsterisk');

            if ($fix) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->addContent($stackPtr, ' ');
                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}
