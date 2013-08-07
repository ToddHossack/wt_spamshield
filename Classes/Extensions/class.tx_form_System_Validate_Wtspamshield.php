<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Ralf Zimmermann <Ralf.Zimmermann@tritum.de>
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

/**
 *
 * you can validate a form element by use of the wtspamshield rule
 * 
 * rules {
 *   1 = wtspamshield
 *   1 {
 *     breakOnError = 
 *     showMessage = 
 *     message = 
 *     error = 
 *     element = email
 *   }
 * 
}
 */

require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Extensions/class.tx_wtspamshield_extensions_abstract.php');

class tx_form_System_Validate_Wtspamshield extends tx_form_System_Validate_Abstract {

	/**
	 * @var tx_wtspamshield_extensions_abstract
	 */
	protected $abstract;

	/**
	 * Constructor
	 *
	 * @param array $arguments
	 * @return void
	 */
	public function __construct($arguments) {
		parent::__construct($arguments);
	}

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
	 * Returns TRUE if submitted value validates according to rule
	 *
	 * @return boolean
	 * @see tx_form_System_Validate_Interface::isValid()
	 */
	public function isValid() {

		if ( $this->getAbstract()->isActivated('standardMailform') ) {
			$error = '';

			if ($this->requestHandler->has($this->fieldName)) {
				$value = $this->requestHandler->getByMethod($this->fieldName);
				$validateArray = array(
					$this->fieldName => $value
				);
				$error = $this->processValidationChain($validateArray);
			}

			if (!empty($error)) { // If error
				$this->setError('', strip_tags($error));
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * @param array $fieldValues
	 * @return string
	 */
	protected function processValidationChain(array $fieldValues) {
		$error = '';

		// 1a. blacklistCheck
		if (!$error) {
			/** @var $method_blacklist_instance tx_wtspamshield_method_blacklist */
			$method_blacklist_instance = t3lib_div::makeInstance('tx_wtspamshield_method_blacklist'); // Generate Instance for session method
			$error .= $method_blacklist_instance->checkBlacklist($fieldValues);
		}

		// 1c. httpCheck
		if (!$error) {
			/** @var $method_httpcheck_instance tx_wtspamshield_method_httpcheck */
			$method_httpcheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_httpcheck'); // Generate Instance for httpCheck method
			$error .= $method_httpcheck_instance->httpCheck($fieldValues);
		}

		// 1e. honeypotCheck
		if (!$error) {
			/** @var $method_honeypot_instance tx_wtspamshield_method_honeypot */
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['standardMailform'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot'); // Generate Instance for honeypot method
			$method_honeypot_instance->inputName = $honeypot_inputName; // name for input field
			$error .= $method_honeypot_instance->checkHoney($fieldValues);
		}

		// 2a. Safe log file
		if ($error) {
			/** @var $method_log_instance tx_wtspamshield_log */
			$method_log_instance = t3lib_div::makeInstance('tx_wtspamshield_log'); // Generate Instance for logging method
			$method_log_instance->dbLog('standardMailform', $error, $fieldValues);
		}

		// 2b. Send email to admin
		if ($error) {
			/** @var $method_sendEmail_instance tx_wtspamshield_mail */
			$method_sendEmail_instance = t3lib_div::makeInstance('tx_wtspamshield_mail'); // Generate Instance for email method
			$method_sendEmail_instance->sendEmail('standardMailform', $error, $fieldValues);
		}

		return $error;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_form_System_Validate_Wtspamshield.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_form_System_Validate_Wtspamshield.php']);
}

?>
