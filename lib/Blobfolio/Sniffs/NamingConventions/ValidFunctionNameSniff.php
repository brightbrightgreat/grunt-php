<?php
//---------------------------------------------------------------------
// Valid Function Name
//---------------------------------------------------------------------
// modified Squiz standard:
//
// 1. prefer snake_case to camelCase (warning)

/**
 * Blobfolio_Sniffs_NamingConventions_ValidFunctionNameSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Blobfolio Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PEAR_Sniffs_NamingConventions_ValidFunctionNameSniff', true) === false) {
	throw new PHP_CodeSniffer_Exception('Class PEAR_Sniffs_NamingConventions_ValidFunctionNameSniff not found');
}

/**
 * Blobfolio_Sniffs_NamingConventions_ValidFunctionNameSniff.
 *
 * Ensures method names are correct depending on whether they are public
 * or private, and that functions are named correctly.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Blobfolio Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: 2.6.1
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Blobfolio_Sniffs_NamingConventions_ValidFunctionNameSniff extends PEAR_Sniffs_NamingConventions_ValidFunctionNameSniff
{
	/**
	 * Processes the tokens within the scope.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
	 * @param int                  $stackPtr  The position where this token was
	 *                                        found.
	 * @param int                  $currScope The position of the current scope.
	 *
	 * @return void
	 */
	protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $currScope) {

		$methodName = $phpcsFile->getDeclarationName($stackPtr);
		if ($methodName === null) {
			// Ignore closures.
			return;
		}

		$className = $phpcsFile->getDeclarationName($currScope);
		$errorData = array($className.'::'.$methodName);

		// Is this a magic method. i.e., is prefixed with "__" ?
		if (preg_match('|^__|', $methodName) !== 0) {
			$magicPart = strtolower(substr($methodName, 2));
			if (isset($this->magicMethods[$magicPart]) === false) {
				 $error = 'Method name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
				 $phpcsFile->addError($error, $stackPtr, 'MethodDoubleUnderscore', $errorData);
			}

			return;
		}

		// PHP4 constructors are allowed to break our rules.
		if ($methodName === $className) {
			return;
		}

		// PHP4 destructors are allowed to break our rules.
		if ($methodName === '_'.$className) {
			return;
		}

		$methodProps    = $phpcsFile->getMethodProperties($stackPtr);
		$scope          = $methodProps['scope'];
		$scopeSpecified = $methodProps['scope_specified'];

		if ($methodProps['scope'] === 'private') {
			$isPublic = false;
		} else {
			$isPublic = true;
		}

		// If the scope was specified on the method, then the method must be
		// camel caps and an underscore should be checked for. If it wasn't
		// specified, treat it like a public method and remove the underscore
		// prefix if there is one because we cant determine if it is private or
		// public.
		$testMethodName = $methodName;
		if ($scopeSpecified === false && $methodName{0} === '_') {
			$testMethodName = substr($methodName, 1);
		}

		if (preg_match('/A-Z/', $testMethodName) && !preg_match('/^[a-z_\d]+[A-Z]+$/', $testMethodName)) {
			$error = 'Function name "%s" is in camel caps format; use lowercase and underscores instead.';
			$phpcsFile->addWarning($error, $stackPtr, 'CamelCaps', $errorData);
		}

	}//end processTokenWithinScope()


	/**
	 * Processes the tokens outside the scope.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
	 * @param int                  $stackPtr  The position where this token was
	 *                                        found.
	 *
	 * @return void
	 */
	protected function processTokenOutsideScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {

		$functionName = $phpcsFile->getDeclarationName($stackPtr);
		if ($functionName === null) {
			return;
		}

		$errorData = array($functionName);

		// Does this function claim to be magical?
		if (preg_match('|^__|', $functionName) !== 0) {
			$error = 'Function name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
			$phpcsFile->addError($error, $stackPtr, 'DoubleUnderscore', $errorData);
			return;
		}

		if (preg_match('/A-Z/', $functionName) && !preg_match('/^[a-z_\d]+[A-Z]+$/', $functionName)) {
			$error = 'Function name "%s" is in camel caps format; use lowercase and underscores instead.';
			$phpcsFile->addWarning($error, $stackPtr, 'CamelCaps', $errorData);
		}

	}//end processTokenOutsideScope()


}//end class

?>