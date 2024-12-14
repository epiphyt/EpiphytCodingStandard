<?php

namespace EpiphytCodingStandard\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class EmptyLineIndentationSniff implements Sniff
{
    public function register()
    {
        return [T_WHITESPACE];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Skip if the token is not an empty line
        if (trim($tokens[$stackPtr]['content']) !== '') {
            return;
        }
        
        if (!str_contains($tokens[$stackPtr]['content'], PHP_EOL)) {
            return;
        }

        // Find the previous non-empty line
        $prevLinePtr = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        
        // Check indentation
        if ($prevLinePtr !== false && $tokens[$stackPtr]['line'] !== $tokens[$prevLinePtr]['line']) {
            $isError = false;
            
            if (isset($tokens[$stackPtr]['orig_content'])) {
                $i = 1;
                $prevLineTokens = $this->getTokensOfLine($phpcsFile, ($tokens[$stackPtr]['line'] - $i));
                $prevLineFirstToken = \reset($prevLineTokens);
                
                // get the starting line of a multiline quotation
                while ($prevLineFirstToken['type'] === 'T_DOUBLE_QUOTED_STRING' || $prevLineFirstToken['type'] === 'T_CONSTANT_ENCAPSED_STRING') {
                    ++$i;
                    $prevLineTokens = $this->getTokensOfLine($phpcsFile, ($tokens[$stackPtr]['line'] - $i));
                    $prevLineFirstToken = \reset($prevLineTokens);
                }
                
                if (!empty($prevLineFirstToken)) {
                    if (
                        isset($prevLineFirstToken['orig_content'])
                        && isset($tokens[$stackPtr]['orig_content'])
                        && (
                            $tokens[$stackPtr]['orig_content'] === $prevLineFirstToken['orig_content']
                            || $tokens[$stackPtr]['orig_content'] === $prevLineFirstToken['orig_content'] . \PHP_EOL
                        )
                    ) {
                        $isError = false;
                    }
                    else {
                        $isError = true;
                    }
                }
            }
            else {
                if ($tokens[$prevLinePtr]['level'] === 0 && str_contains($tokens[$stackPtr]['content'], ' ')) {
                    $isError = true;
                }
                else if ($tokens[$prevLinePtr]['level'] > 0) {
                    $isError = true;
                }
            }
            
            if ($isError) {
                $error = 'Empty lines must have the same indentation level as the line before';
                $phpcsFile->addError($error, $stackPtr, 'IncorrectIndentation');
            }
        }
    }
    
    private function getTokensOfLine(File $phpcsFile, $line)
    {
        $tokens = $phpcsFile->getTokens();
        $lineTokens = [];

        foreach ($tokens as $ptr => $token) {
            if ($token['line'] === $line) {
                $lineTokens[$ptr] = $token;
            }
        }

        return $lineTokens;
    }
}
