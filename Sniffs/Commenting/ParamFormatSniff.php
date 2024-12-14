<?php

namespace EpiphytCodingStandard\Sniffs\Comments;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class ParamFormatSniff implements Sniff
{
    public function register()
    {
        return [T_DOC_COMMENT_STRING];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $tagPos = $phpcsFile->findPrevious(T_DOC_COMMENT_TAG, ($stackPtr - 1), null, false, null, true);

        if ($tagPos === false) {
            return;
        }

        if ($tokens[$tagPos]['line'] < $tokens[$stackPtr]['line']) {
            return;
        }

        $commentStart = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, ($stackPtr - 1));

        // inline declarations are fine
        if ($tokens[$tagPos]['line'] === $tokens[$commentStart]['line']) {
            return;
        }

        // check the format
        switch ($tokens[$tagPos]['content']) {
            case '@param':
                $content = $phpcsFile->getTokensAsString($tagPos, 3, true);

                if (\preg_match('/\@param\t+([\w\[\]\|\\]+)\t+\$\w+ ([^\s]*)/', $content) !== false) {
                    $phpcsFile->addError(
                        'Invalid parameter format. Properties must be separated by type and parameter must contain a description.',
                        $tagPos,
                        'InvalidFormat'
                    );
                }
            break;

            case '@return':
                $content = $phpcsFile->getTokensAsString($tagPos, 3, true);

                if (\preg_match('/\@return\t+([\w\[\]\|\\]+) ([^\s]*)/', $content) !== false) {
                    $phpcsFile->addError(
                        'Invalid parameter format. A tab must follow the @return with a type and a description separated by a single space.',
                        $tagPos,
                        'InvalidFormat'
                    );
                }
            break;

            default:
                $content = $phpcsFile->getTokensAsString($tagPos, 3, true);

                if (\preg_match('/' . \preg_quote($tokens[$tagPos]['content'], '/') . '\t+([^\s]+)/', $content) !== false) {
                    $phpcsFile->addError(
                        'Invalid parameter format. A tab must follow the @command.',
                        $tagPos,
                        'InvalidFormat'
                    );
                }
            break;
        }
    }
}
