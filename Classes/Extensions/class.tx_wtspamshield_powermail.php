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

class tx_wtspamshield_powermail extends tslib_pibase {

	var $prefix_inputName = 'tx_powermail_pi1'; 
	
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
	 * Function PM_FormWrapMarkerHook() to manipulate whole formwrap
	 *
	 * @param	array	$OuterMarkerArray: Marker Array out of the loop from powermail
	 * @param	array	$subpartArray: subpartArray Array from powermail
	 * @param	array	$conf: ts configuration from powermail
	 * @param	array	$obj: Parent Object
	 * @return	void
	 */
	function PM_FormWrapMarkerHook($OuterMarkerArray, &$subpartArray, $conf, $obj) {
		
		if ( $this->getAbstract()->isActivated('powermail') ) {
			// 1. check Extension Manager configuration
			$this->getAbstract()->getDiv()->checkConf(); 
			
			// 2. Set session on form create
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$method_session_instance->setSessionTime(); // Start setSessionTime() Function: Set session if form is loaded
			
			// 3. Add Honeypot
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['powermail'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot'); // Generate Instance for honeypot method
			$method_honeypot_instance->inputName = $honeypot_inputName; // name for input field
			$method_honeypot_instance->prefix_inputName = $this->prefix_inputName; // prefix
			$subpartArray['###POWERMAIL_CONTENT###'] .= $method_honeypot_instance->createHoneypot(); // Add honeypot to content
		}
		
	} 
	
	
	/**
	 * Function PM_FieldWrapMarkerHook() to manipulate Fieldwraps
	 *
	 * @param	array	$obj: Parent Object
	 * @param	array	$markerArray: Marker Array from powermail
	 * @param	array	$sessiondata: Values from powermail Session
	 * @return	string	If not false is returned, powermail will show an error. If string is returned, powermail will show this string as errormessage
	 */
	function PM_SubmitBeforeMarkerHook($obj, $markerArray = array(), $sessiondata = array()) {
		// config
		$error = ''; // no error at the beginning

		if ( $this->getAbstract()->isActivated('powermail') ) {

			$error = $this->processValidationChain($sessiondata);

			// 2c. Return Error message if exists
			if (!empty($error)) { // If error
				return '<div class="wtspamshield-errormsg">' . $error . '</div>';
			}
		}
		
		return false;
	}

	/**
	 * @param array $fieldValues
	 * @return string
	 */
	protected function processValidationChain(array $fieldValues) {
		$error = '';

		// 1a. blacklistCheck
		if (!$error) {
			$method_blacklist_instance = t3lib_div::makeInstance('tx_wtspamshield_method_blacklist'); // Generate Instance for session method
			$error .= $method_blacklist_instance->checkBlacklist($fieldValues);
		}

		// 1b. sessionCheck
		if (!$error) {
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$error .= $method_session_instance->checkSessionTime();
		}
		
		// 1c. httpCheck
		if (!$error) {
			$method_httpcheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_httpcheck'); // Generate Instance for httpCheck method
			$error .= $method_httpcheck_instance->httpCheck($fieldValues);
		}
		
		// 1d. uniqueCheck
		if (!$error) {
			$method_unique_instance = t3lib_div::makeInstance('tx_wtspamshield_method_unique'); // Generate Instance for uniqueCheck method
			$error .= $method_unique_instance->main($fieldValues);
		}
		
		// 1e. honeypotCheck
		if (!$error) {
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['powermail'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot'); // Generate Instance for honeypot method
			$method_honeypot_instance->inputName = $honeypot_inputName; // name for input field
			$error .= $method_honeypot_instance->checkHoney($fieldValues);
		}
		
		// 1f. Akismet Check
		if (!$error) {
			// get GPvars, downwards compatibility
			$T3Version = class_exists('t3lib_utility_VersionNumber')
				? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version)
				: t3lib_div::int_from_ver(TYPO3_version);
			if ($T3Version < 4006000) {
				$form = t3lib_div::GPvar('tx_powermail_pi1');
			} else {
				$form = t3lib_div::_GP('tx_powermail_pi1');
			}		
			$method_akismet_instance = t3lib_div::makeInstance('tx_wtspamshield_method_akismet'); // Generate Instance for Akismet method
			$error .= $method_akismet_instance->checkAkismet($form, 'powermail');
		}
		
		// 2a. Safe log file
		if ($error) {
			$method_log_instance = t3lib_div::makeInstance('tx_wtspamshield_log'); // Generate Instance for logging method
			$method_log_instance->dbLog('powermail', $error, $fieldValues);
		}
		
		// 2b. Send email to admin
		if ($error) {
			$method_sendEmail_instance = t3lib_div::makeInstance('tx_wtspamshield_mail'); // Generate Instance for email method
			$method_sendEmail_instance->sendEmail('powermail', $error, $fieldValues);
		}

		return $error;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_powermail.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_powermail.php']);
}

?>
