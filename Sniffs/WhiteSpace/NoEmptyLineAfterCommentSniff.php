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
                    $fix = $phpcsFile->addFixableError('An empty line after a comment is not allowed', $stackPtr, 'NoEmptyLineAfterComment');

                    if ($fix) {
                        $eol = $phpcsFile->eolChar;

                        // Collect every whitespace token between the comment and the next
                        // token. Prefer orig_content so the next line's tabs are preserved
                        // instead of the spaces produced by --tab-width expansion.
                        $whitespace = '';
                        for ($i = ($stackPtr + 1); $i < $nextToken; $i++) {
                            $whitespace .= ($tokens[$i]['orig_content'] ?? $tokens[$i]['content']);
                        }

                        // Indentation of the next line is whatever follows the last newline.
                        $lastEol = strrpos($whitespace, "\n");
                        $indent = '';
                        
                        if ($lastEol !== false) {
                            $indent = substr($whitespace, ($lastEol + 1));
                        }

                        // Keep a single line break: the comment token already provides one
                        // when its content ends with a newline, otherwise add one ourselves.
                        $commentEndsWithEol = false;
                        
                        if (str_ends_with($tokens[$stackPtr]['content'], $eol) || str_ends_with($tokens[$stackPtr]['content'], "\n")) {
                            $commentEndsWithEol = true;
                        }
                        
                        $replacement = '';
                        
                        if (!$commentEndsWithEol) {
                            $replacement = $eol;
                        }
                        
                        $replacement .= $indent;

                        $phpcsFile->fixer->beginChangeset();

                        for ($i = ($stackPtr + 1); $i < ($nextToken - 1); $i++) {
                            $phpcsFile->fixer->replaceToken($i, '');
                        }

                        $phpcsFile->fixer->replaceToken(($nextToken - 1), $replacement);
                        $phpcsFile->fixer->endChangeset();
                    }
                }
            }
        }
    }
}
