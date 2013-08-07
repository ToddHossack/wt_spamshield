<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Bjoern Jacob <bjoern.jacob@tritum.de>
*  based on Code of Alexander Kellner <Alexander.Kellner@einpraegsam.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Extensions/class.tx_wtspamshield_extensions_abstract.php');

class tx_wtspamshield_t3blog extends tslib_pibase {

	/**
	 * @var tx_wtspamshield_extensions_abstract
	 */
	protected $abstract;
	
	/**
	 * @return tx_wtspamshield_div
	 */
	protected function getAbstract() {
		if (!isset($this->abstract)) {
			$this->abstract = t3lib_div::makeInstance('tx_wtspamshield_extensions_abstract');
		}
		return $this->abstract;
	}

	/**
	* Implementation of Hook "insertNewComment" from t3_blog
	* @param	array		$params The parameters
	* @param	object		$reference The refering object
	* @return	void
	*/
	public function insertNewComment(&$params, &$reference) {
		// config
		$error = ''; // no error at the beginning

		$validateArray = $params['data'];

		if ( $this->getAbstract()->isActivated('t3_blog') ) {

			$error = $this->processValidationChain($validateArray);

			if (!empty($error)) {
				// Right now we cannot set errorMessage because it is protected, see forge.typo3.org #42615
				//$reference->errorMessage = $error;
				// Mark as spam
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($params['table'], 'uid = '.$params['commentUid'], array('spam' => 1));
			}
		}
	}

	/**
	 * @param array $fieldValues
	 * @return string
	 */
	protected function processValidationChain(array $fieldValues) {
		$error = '';

		// 1a. blacklistCheck - we don't need it since t3_blog has already an own implementation
		
		// 1b. nameCheck - doesn't make sense since there are no 2 fields for first and last name
		
		// 1c. httpCheck
		if (!$error) {
			$method_httpcheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_httpcheck'); // Generate Instance for http method
			$error .= $method_httpcheck_instance->httpCheck($fieldValues);
		}
		
		// 1d. sessionCheck - not possible, can't start time since there is no hook when form is rendered
		
		// 1e. honeypotCheck - not possible, can't insert form field since there is no hook when form is rendered
		
		// 1f. Akismet Check
		if (!$error) {
			$method_akismet_instance = t3lib_div::makeInstance('tx_wtspamshield_method_akismet'); // Generate Instance for Akismet method
			$error .= $method_akismet_instance->checkAkismet($fieldValues, 't3_blog');
		}

		// 2a. Safe log file
		if ($error) {
			$method_log_instance = t3lib_div::makeInstance('tx_wtspamshield_log'); // Generate Instance for logging method
			$method_log_instance->dbLog('t3_blog', $error, $fieldValues);
		}
		
		// 2b. Send email to admin
		if ($error) {
			$method_sendEmail_instance = t3lib_div::makeInstance('tx_wtspamshield_mail'); // Generate Instance for email method
			$method_sendEmail_instance->sendEmail('t3_blog', $error, $fieldValues);
		}

		return $error;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_t3blog.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_t3blog.php']);
}

?>
