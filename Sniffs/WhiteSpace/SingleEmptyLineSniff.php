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

        // PHP_CodeSniffer splits a run of whitespace into one token per line.
        // Only act from the first token of the run so the whole block of empty
        // lines is handled exactly once.
        if ($stackPtr > 0 && $tokens[($stackPtr - 1)]['code'] === T_WHITESPACE) {
            return;
        }

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $stackPtr, null, true);

        if ($prevToken === false || $nextToken === false) {
            return;
        }

        // More than one empty line between the previous and the next content.
        if (($tokens[$nextToken]['line'] - $tokens[$prevToken]['line']) <= 2) {
            return;
        }

        $fix = $phpcsFile->addFixableError('Only a single empty line is allowed', $stackPtr, 'NoWhitespaceAtLineEnd');

        if ($fix) {
            $eol = $phpcsFile->eolChar;

            // Collect the whole whitespace run, preferring orig_content so the
            // next line's tabs survive --tab-width expansion.
            $whitespace = '';
            for ($i = $stackPtr; $i < $nextToken; $i++) {
                $whitespace .= ($tokens[$i]['orig_content'] ?? $tokens[$i]['content']);
            }

            // Keep the line break ending the previous line, a single empty line
            // (with its original indentation) and the next line's indentation.
            $parts = explode($eol, $whitespace);
            $replacement = $parts[0] . $eol . $parts[1] . $eol . $parts[(count($parts) - 1)];

            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($stackPtr, $replacement);

            for ($i = ($stackPtr + 1); $i < $nextToken; $i++) {
                $phpcsFile->fixer->replaceToken($i, '');
            }

            $phpcsFile->fixer->endChangeset();
        }
    }
}
