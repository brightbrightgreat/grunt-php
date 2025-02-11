<?php
/**
 * Squiz_Sniffs_Commenting_InlineCommentSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Squiz_Sniffs_Commenting_InlineCommentSniff.
 *
 * Checks that there is adequate spacing between comments.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Blobfolio_Sniffs_Commenting_InlineCommentSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
                                   'PHP',
                                   'JS',
                                  );


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_COMMENT,
                T_DOC_COMMENT_OPEN_TAG,
               );

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // If this is a function/class/interface doc block comment, skip it.
        // We are only interested in inline doc block comments, which are
        // not allowed.
        if ($tokens[$stackPtr]['code'] === T_DOC_COMMENT_OPEN_TAG) {
            $nextToken = $phpcsFile->findNext(
                PHP_CodeSniffer_Tokens::$emptyTokens,
                ($stackPtr + 1),
                null,
                true
            );

            $ignore = array(
                       T_CLASS,
                       T_INTERFACE,
                       T_TRAIT,
                       T_FUNCTION,
                       T_CLOSURE,
                       T_PUBLIC,
                       T_PRIVATE,
                       T_PROTECTED,
                       T_FINAL,
                       T_STATIC,
                       T_ABSTRACT,
                       T_CONST,
                       T_PROPERTY,
                       T_INCLUDE,
                       T_INCLUDE_ONCE,
                       T_REQUIRE,
                       T_REQUIRE_ONCE,
                      );

            if (in_array($tokens[$nextToken]['code'], $ignore) === true) {
                return;
            }

            if ($phpcsFile->tokenizerType === 'JS') {
                // We allow block comments if a function or object
                // is being assigned to a variable.
                $ignore    = PHP_CodeSniffer_Tokens::$emptyTokens;
                $ignore[]  = T_EQUAL;
                $ignore[]  = T_STRING;
                $ignore[]  = T_OBJECT_OPERATOR;
                $nextToken = $phpcsFile->findNext($ignore, ($nextToken + 1), null, true);
                if ($tokens[$nextToken]['code'] === T_FUNCTION
                    || $tokens[$nextToken]['code'] === T_CLOSURE
                    || $tokens[$nextToken]['code'] === T_OBJECT
                    || $tokens[$nextToken]['code'] === T_PROTOTYPE
                ) {
                    return;
                }
            }

            $prevToken = $phpcsFile->findPrevious(
                PHP_CodeSniffer_Tokens::$emptyTokens,
                ($stackPtr - 1),
                null,
                true
            );

            if ($tokens[$prevToken]['code'] === T_OPEN_TAG) {
                return;
            }
        }//end if

        // We don't want end of block comments. If the last comment is a closing
        // curly brace.
        $previousContent = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
        if ($tokens[$previousContent]['line'] === $tokens[$stackPtr]['line']) {
            if ($tokens[$previousContent]['code'] === T_CLOSE_CURLY_BRACKET) {
                return;
            }

            // Special case for JS files.
            if ($tokens[$previousContent]['code'] === T_COMMA
                || $tokens[$previousContent]['code'] === T_SEMICOLON
            ) {
                $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($previousContent - 1), null, true);
                if ($tokens[$lastContent]['code'] === T_CLOSE_CURLY_BRACKET) {
                    return;
                }
            }
        }

        $comment = rtrim($tokens[$stackPtr]['content']);

        // Only want inline comments.
        if (substr($comment, 0, 2) !== '//') {
            return;
        }

        if (trim(substr($comment, 2)) !== '') {
            $spaceCount = 0;
            $tabFound   = false;

            $commentLength = strlen($comment);
            for ($i = 2; $i < $commentLength; $i++) {
                if ($comment[$i] === "\t") {
                    $tabFound = true;
                    break;
                }

                if ($comment[$i] !== ' ') {
                    break;
                }

                $spaceCount++;
            }
        }//end if

        // The below section determines if a comment block is correctly capitalised,
        // and ends in a full-stop. It will find the last comment in a block, and
        // work its way up.
        $nextComment = $phpcsFile->findNext(array(T_COMMENT), ($stackPtr + 1), null, false);
        if (($nextComment !== false)
            && (($tokens[$nextComment]['line']) === ($tokens[$stackPtr]['line'] + 1))
        ) {
            return;
        }

        $topComment  = $stackPtr;
        $lastComment = $stackPtr;
        while (($topComment = $phpcsFile->findPrevious(array(T_COMMENT), ($lastComment - 1), null, false)) !== false) {
            if ($tokens[$topComment]['line'] !== ($tokens[$lastComment]['line'] - 1)) {
                break;
            }

            $lastComment = $topComment;
        }

        $topComment  = $lastComment;
        $commentText = '';

        for ($i = $topComment; $i <= $stackPtr; $i++) {
            if ($tokens[$i]['code'] === T_COMMENT) {
                $commentText .= trim(substr($tokens[$i]['content'], 2));
            }
        }

        if ($commentText === '') {
            return;
        }

        // Only check the end of comment character if the start of the comment
        // is a letter, indicating that the comment is just standard text.
        if (preg_match('/^\p{L}/u', $commentText) === 1) {
            $commentCloser   = $commentText[(strlen($commentText) - 1)];
            $acceptedClosers = array(
                                'full-stops'        => '.',
                                'semi-colons'       => ';',
                                'exclamation marks' => '!',
                                'or question marks' => '?',
                               );

            if (in_array($commentCloser, $acceptedClosers) === false) {
                $error = 'Inline comments must end in %s';
                $ender = '';
                foreach ($acceptedClosers as $closerName => $symbol) {
                    $ender .= ' '.$closerName.',';
                }

                $ender = trim($ender, ' ,');
                $data  = array($ender);
                $phpcsFile->addError($error, $stackPtr, 'InvalidEndChar', $data);
            }
        }

    }//end process()


}//end class
