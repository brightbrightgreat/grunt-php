<?php
//---------------------------------------------------------------------
// Lowercased Filename
//---------------------------------------------------------------------
// modified Generic standard:
//
// 1. severity to warning

/**
 * Blobfolio_Sniffs_Files_LowercasedFilenameSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Andy Grunwald <andygrunwald@gmail.com>
 * @copyright 2010-2014 Andy Grunwald
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Checks that all file names are lowercased.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Andy Grunwald <andygrunwald@gmail.com>
 * @copyright 2010-2014 Andy Grunwald
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Blobfolio_Sniffs_Files_LowercasedFilenameSniff implements PHP_CodeSniffer_Sniff
{


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {

		return array(T_OPEN_TAG);

	}//end register()


	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token in
	 *                                        the stack passed in $tokens.
	 *
	 * @return int
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {

		$filename = $phpcsFile->getFilename();
		if ($filename === 'STDIN') {
			return;
		}

		$filename          = basename($filename);
		$lowercaseFilename = strtolower($filename);
		if ($filename !== $lowercaseFilename) {
			$data  = array(
					  $filename,
					  $lowercaseFilename,
					 );
			$error = 'Lowercase filenames are preferred.';
			$phpcsFile->addWarning($error, $stackPtr, 'NotFound', $data);
			$phpcsFile->recordMetric($stackPtr, 'Lowercase filename', 'no');
		} else {
			$phpcsFile->recordMetric($stackPtr, 'Lowercase filename', 'yes');
		}

		// Ignore the rest of the file.
		return ($phpcsFile->numTokens + 1);

	}//end process()


}//end class

?>