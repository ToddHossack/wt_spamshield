<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Alexander Kellner <Alexander.Kellner@einpraegsam.net>
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

class tx_wtspamshield_defaultmailform extends tslib_pibase {

	/**
	 * @var array
	 */
	protected $messages = array();

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
	 * Function generateSession() is called if the form is rendered (generate a session)
	 *
	 * @param string $content
	 * @param array $configuration
	 * @return	void
	 */
	function generateSession($content, array $configuration = NULL) {
		if ( $this->getAbstract()->isActivated('standardMailform') ) {
			$this->getDiv()->checkConf(); // Check Extension Manager configuration
			$forceValue = !(isset($configuration['ifOutdated']) && $configuration['ifOutdated']);
			
			// Set session on form create
			/** @var $method_session_instance tx_wtspamshield_method_session */
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session');
			$method_session_instance->setSessionTime($forceValue);
		}

		return $content;
	}
	
	/**
	 * Function sendFormmail_preProcessVariables() is called after submit - stop mail if needed
	 *
	 * @param	object		$form: Form Object
	 * @param	object		$obj: Parent Object
	 * @param	array		$legacyConfArray: legacy configuration
	 * @return	object		$form
	 */
	function sendFormmail_preProcessVariables($form, $obj, $legacyConfArray = array()) {
		if ( $this->getAbstract()->isActivated('standardMailform') ) {
			$error = $this->processValidationChain($form);
			
			// 2c. Redirect and stop mail sending
			if (!empty($error)) { // If error
				$link = (!empty($GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['redirect.']['standardMailform']) ? $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['redirect.']['standardMailform'] : t3lib_div::getIndpEnv('TYPO3_SITE_URL')); // redirection link - take only Domain if no target in TS
				header('HTTP/1.1 301 Moved Permanently'); 
				header('Location: ' . $link); 
				header('Connection: close');
				return false; // no return, so no email will be sent
			}
			
		}
		
		return $form; // default: return values to send email
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

		// 1b. sessionCheck
		if (!$error) {
			/** @var $method_session_instance tx_wtspamshield_method_session */
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$error .= $method_session_instance->checkSessionTime();
		}

		// 1c. httpCheck
		if (!$error) {
			/** @var $method_httpcheck_instance tx_wtspamshield_method_httpcheck */
			$method_httpcheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_httpcheck'); // Generate Instance for httpCheck method
			$error .= $method_httpcheck_instance->httpCheck($fieldValues);
		}

		// 1d. uniqueCheck
		if (!$error) {
			/** @var $method_unique_instance tx_wtspamshield_method_unique */
			$method_unique_instance = t3lib_div::makeInstance('tx_wtspamshield_method_unique'); // Generate Instance for uniqueCheck method
			$error .= $method_unique_instance->main($fieldValues);
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_defaultmailform.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_defaultmailform.php']);
}

?>
