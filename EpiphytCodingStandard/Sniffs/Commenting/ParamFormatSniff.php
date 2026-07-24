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
        $tabWidth = ($phpcsFile->config->tabWidth ?? 0);

        if ($tabWidth < 1) {
            $tabWidth = 4;
        }

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
                $targetStops = $this->paramTargetStops($phpcsFile, $commentStart, $tabWidth);
                $expected = $this->buildParam($tokens[$stackPtr], $tabWidth, $targetStops);
                $separator = '';

                if ($tokens[($stackPtr - 1)]['code'] === T_DOC_COMMENT_WHITESPACE) {
                    $separator = ($tokens[($stackPtr - 1)]['orig_content'] ?? $tokens[($stackPtr - 1)]['content']);
                }
                $actual = ($tokens[$stackPtr]['orig_content'] ?? $tokens[$stackPtr]['content']);

                // @param<tab>type<tabs>$var<space>description, with the variable
                // aligned across the docblock's @param tags (up to five tab stops).
                if ($expected === null || $separator !== "\t" || $actual !== $expected) {
                    $message = 'Invalid parameter format. Properties must be separated by type and parameter must contain a description.';
                    $this->fixOrError($phpcsFile, $stackPtr, $tagPos, $message, $expected);
                }
            break;

            case '@return':
                $content = $phpcsFile->getTokensAsString($tagPos, 3, true);

                // @return<tab>type<space>description
                if (!\preg_match('/^@return\t+\S+ \S/', $content)) {
                    $message = 'Invalid parameter format. A tab must follow the @return with a type and a description separated by a single space.';
                    $this->fixOrError($phpcsFile, $stackPtr, $tagPos, $message, $this->buildReturn($tokens[$stackPtr]));
                }
            break;

            default:
                $content = $phpcsFile->getTokensAsString($tagPos, 3, true);

                // @command<tab>content
                if (!\preg_match('/^' . \preg_quote($tokens[$tagPos]['content'], '/') . '\t+\S/', $content)) {
                    $message = 'Invalid parameter format. A tab must follow the @command.';
                    $this->fixOrError($phpcsFile, $stackPtr, $tagPos, $message, $this->buildDefault($tokens[$stackPtr]));
                }
            break;
        }
    }

    /**
     * Parse an @param value into [full, type, variable, description].
     *
     * The value is anchored on the variable so a type may contain internal
     * spaces (e.g. array<string, int>). The variable may be passed by reference
     * (&$var) or variadic (...$var). Returns null when the value cannot be
     * safely reconstructed (e.g. a missing variable or description, which we
     * cannot invent, or a tab inside the type that the fix would mistake for a
     * separator).
     */
    private function parseParam(array $stringToken)
    {
        $value = \trim(($stringToken['orig_content'] ?? $stringToken['content']));

        // type (anything up to the variable), the $variable and a description.
        if (!\preg_match('/^(\S.*?)\s+(&?(?:\.\.\.)?\$\w+)\s+(\S.*)$/', $value, $matches)) {
            return null;
        }

        // A tab in the type would be mistaken for a separator after the fix.
        if (\strpos($matches[1], "\t") !== false) {
            return null;
        }

        return $matches;
    }

    /**
     * Determine the tab stop the variables of a docblock's parameter tags should
     * align to. Each type occupies whole tab stops; the target is one stop past
     * the widest type, so the widest type gets a single separating tab and the
     * narrower ones pad up to the same column. The minimum is two stops, so short
     * types (int) always reach two stops while wider types still need only one
     * tab. A type that would need more than five stops is ignored here: it does
     * not raise the target (the rest align to the widest remaining type) and
     * keeps a single tab of its own, sticking out past the column.
     */
    private function paramTargetStops(File $phpcsFile, $commentStart, $tabWidth)
    {
        $tokens = $phpcsFile->getTokens();
        $closer = ($tokens[$commentStart]['comment_closer'] ?? null);

        // At least two stops, so a type narrower than a tab stop (e.g. int) still
        // gets two tabs. Wider types span a stop already and reach the second one
        // with a single tab, so this minimum never adds a tab to them.
        if ($closer === null) {
            return 2;
        }

        $stops = 2;

        for ($i = ($commentStart + 1); $i < $closer; $i++) {
            if ($tokens[$i]['code'] !== T_DOC_COMMENT_TAG || $tokens[$i]['content'] !== '@param') {
                continue;
            }

            $stringPos = $phpcsFile->findNext(T_DOC_COMMENT_STRING, ($i + 1), $closer);

            if ($stringPos === false || $tokens[$stringPos]['line'] !== $tokens[$i]['line']) {
                continue;
            }

            $matches = $this->parseParam($tokens[$stringPos]);

            if ($matches === null) {
                continue;
            }

            $candidate = (\intdiv(\strlen($matches[1]), $tabWidth) + 1);

            // Over-wide types are ignored: they keep a single tab and the others
            // align to the widest type that still fits within five stops.
            if ($candidate > 11) {
                continue;
            }

            if ($candidate > $stops) {
                $stops = $candidate;
            }
        }

        return $stops;
    }

    /**
     * Build the corrected string content for an @param tag, padding the type
     * with tabs so the variable lands on the shared target tab stop.
     */
    private function buildParam(array $stringToken, $tabWidth, $targetStops)
    {
        $matches = $this->parseParam($stringToken);

        if ($matches === null) {
            return null;
        }

        $tabs = ($targetStops - \intdiv(\strlen($matches[1]), $tabWidth));

        if ($tabs < 1) {
            $tabs = 1;
        }

        return $matches[1] . \str_repeat("\t", $tabs) . $matches[2] . ' ' . $matches[3];
    }

    /**
     * Build the corrected string content for an @return tag.
     */
    private function buildReturn(array $stringToken)
    {
        $value = \trim(($stringToken['orig_content'] ?? $stringToken['content']));

        // type (no whitespace) and a description.
        if (!\preg_match('/^(\S+)\s+(\S.*)$/', $value, $matches)) {
            return null;
        }

        return $matches[1] . ' ' . $matches[2];
    }

    /**
     * Build the corrected string content for any other tag.
     *
     * These only require a tab followed by some content, so the existing
     * content is reused verbatim (just trimmed of stray leading whitespace).
     */
    private function buildDefault(array $stringToken)
    {
        $value = \ltrim(($stringToken['orig_content'] ?? $stringToken['content']));

        if ($value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Add a fixable error when a corrected value is available, otherwise a
     * plain error. The fix normalises the separator after the tag to a single
     * tab and rewrites the value token with the rebuilt content.
     */
    private function fixOrError(File $phpcsFile, $stackPtr, $tagPos, $message, $fixedValue)
    {
        $tokens = $phpcsFile->getTokens();
        $wsPos = ($stackPtr - 1);

        // We can only fix the canonical "tag, whitespace, value" layout.
        $fixable = false;

        if (
            $fixedValue !== null
            && $wsPos > $tagPos
            && $tokens[$wsPos]['code'] === T_DOC_COMMENT_WHITESPACE
        ) {
            $fixable = true;
        }

        if (!$fixable) {
            $phpcsFile->addError($message, $tagPos, 'InvalidFormat');

            return;
        }

        $fix = $phpcsFile->addFixableError($message, $tagPos, 'InvalidFormat');

        if ($fix) {
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($wsPos, "\t");
            $phpcsFile->fixer->replaceToken($stackPtr, $fixedValue);
            $phpcsFile->fixer->endChangeset();
        }
    }
}
