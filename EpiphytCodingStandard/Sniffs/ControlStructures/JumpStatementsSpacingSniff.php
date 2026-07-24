<?php 
namespace EpiphytCodingStandard\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use SlevomatCodingStandard\Helpers\SniffSettingsHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;
use SlevomatCodingStandard\Sniffs\ControlStructures\JumpStatementsSpacingSniff as SlevomatJumpStatementsSpacingSniff;

use function array_key_exists;
use function in_array;
use const T_OPEN_TAG;
use const T_RETURN;
use const T_YIELD;
use const T_YIELD_FROM;

class JumpStatementsSpacingSniff extends SlevomatJumpStatementsSpacingSniff
{
    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param int $jumpStatementPointer
     */
    public function process(File $phpcsFile, $jumpStatementPointer): void
    {
        $this->linesCountBefore = SniffSettingsHelper::normalizeInteger($this->linesCountBefore);
        $this->linesCountBeforeFirst = SniffSettingsHelper::normalizeInteger($this->linesCountBeforeFirst);
        $this->linesCountBeforeWhenFirstInCaseOrDefault = SniffSettingsHelper::normalizeNullableInteger(
            $this->linesCountBeforeWhenFirstInCaseOrDefault
        );
        $this->linesCountAfter = SniffSettingsHelper::normalizeInteger($this->linesCountAfter);
        $this->linesCountAfterLast = SniffSettingsHelper::normalizeInteger($this->linesCountAfterLast);
        $this->linesCountAfterWhenLastInCaseOrDefault = SniffSettingsHelper::normalizeNullableInteger(
            $this->linesCountAfterWhenLastInCaseOrDefault
        );
        $this->linesCountAfterWhenLastInLastCaseOrDefault = SniffSettingsHelper::normalizeNullableInteger(
            $this->linesCountAfterWhenLastInLastCaseOrDefault
        );

        if ($this->isOneOfYieldSpecialCases($phpcsFile, $jumpStatementPointer)) {
            return;
        }
        
        $tokens = $phpcsFile->getTokens();
        $previousOpenTag = $phpcsFile->findPrevious(T_OPEN_TAG, ($jumpStatementPointer - 1));
        
        if ($tokens[$jumpStatementPointer]['line'] === ($tokens[$previousOpenTag]['line'] + 1)) {
            return;
        }
        
        $previousCommentTag = $phpcsFile->findPrevious(\T_COMMENT, ($jumpStatementPointer - 1));
        
        if ($tokens[$jumpStatementPointer]['line'] === ($tokens[$previousCommentTag]['line'] + 1)) {
            return;
        }

        parent::process($phpcsFile, $jumpStatementPointer);
    }
    
    private function isOneOfYieldSpecialCases(File $phpcsFile, int $jumpStatementPointer): bool
    {
        $tokens = $phpcsFile->getTokens();

        $jumpStatementToken = $tokens[$jumpStatementPointer];
        if ($jumpStatementToken['code'] !== T_YIELD && $jumpStatementToken['code'] !== T_YIELD_FROM) {
            return false;
        }

        // check if yield is used inside parentheses (function call, while, ...)
        if (array_key_exists('nested_parenthesis', $jumpStatementToken)) {
            return true;
        }

        $pointerBefore = TokenHelper::findPreviousEffective($phpcsFile, ($jumpStatementPointer - 1));

        // check if yield is used in assignment
        if (in_array($tokens[$pointerBefore]['code'], Tokens::$assignmentTokens, true)) {
            return true;
        }

        // check if yield is used in a return statement
        return $tokens[$pointerBefore]['code'] === T_RETURN;
    }
}
