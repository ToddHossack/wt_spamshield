<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Stefan Froemken <froemken@gmail.com>
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

class tx_wtspamshield_ke_userregister extends tslib_pibase {

	var $prefix_inputName = 'tx_keuserregister_pi1';

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
	 * Function is called if form is rendered (set tstamp in session)
	 *
	 * @param	array		$markerArray: Array with markers
	 * @param	object		$pObj: parent object
	 * @param	array		$errors: Array with errors
	 * @return	void
	 */
	function additionalMarkers(&$markerArray, $pObj, $errors) {
		if ( $this->getAbstract()->isActivated('ke_userregister') ) {
			// 1. check Extension Manager configuration
			$this->getAbstract()->getDiv()->checkConf(); // Check Extension Manager configuration

			// 2. Session check - generate session entry
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$method_session_instance->setSessionTime(); // Start setSessionTime() Function: Set session if form is loaded

			// 3. Honeypot check - generate honeypot Input field
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['ke_userregister'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot'); // Generate Instance for honeypot method
			$method_honeypot_instance->inputName = $honeypot_inputName;
			$method_honeypot_instance->prefix_inputName = $this->prefix_inputName;
			$pObj->templateCode = str_replace('</form>', $method_honeypot_instance->createHoneypot() . '</form>', $pObj->templateCode); // add input field
		}
	}

	/**
	 * Function processSpecialEvaluations is called from a ke_userregister hook and gives the possibility to disable the db entry of the registration
	 *
	 * @param	array		$errors: generated errors till now
	 * @param	object		$pObj: parent object
	 * @return	void
	 */
	public function processSpecialEvaluations(&$errors, &$pObj) {
		// execute this hook only if there are no other errors
		if (is_array($errors) && count($errors)) return;

		$error = ''; // no error at the beginning

		// get GPvars, downwards compatibility
		if (t3lib_div::int_from_ver(TYPO3_version) < 4006000) {
			$validateArray = t3lib_div::GPvar('tx_keuserregister_pi1');
		} else {
			$validateArray = t3lib_div::_GP('tx_keuserregister_pi1');
		}

		if ( $this->getAbstract()->isActivated('ke_userregister') ) {

			$error = $this->processValidationChain($validateArray);
			// 2c. Error message
			if ($error) {
				// Workaround: create field via TS and put it in HTML template of ke_userregister
				$errors['wt_spamshield'] = $error;
			}
		}
	}

	/**
	 * @param array $fieldValues
	 * @return string
	 */
	protected function processValidationChain(array $fieldValues) {
		$error = '';

		// 1a. nameCheck
		if (!$error) {
			$method_namecheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_namecheck'); // Generate Instance for namecheck method
			$error .= $method_namecheck_instance->nameCheck($fieldValues['first_name'], $fieldValues['last_name']);
		}

		// 1b. httpCheck
		if (!$error) {
			$method_httpcheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_httpcheck'); // Generate Instance for http method
			$error .= $method_httpcheck_instance->httpCheck($fieldValues);
		}

		// 1c. sessionCheck
		if (!$error) {
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$error .= $method_session_instance->checkSessionTime();
		}

		// 1d. honeypotCheck
		if (!$error) {
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['ke_userregister'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot'); // Generate Instance for honeypot method
			$method_honeypot_instance->inputName = $honeypot_inputName; // name for input field
			$error .= $method_honeypot_instance->checkHoney($fieldValues);
		}

		// 1e. Akismet Check
		if (!$error) {
			$method_akismet_instance = t3lib_div::makeInstance('tx_wtspamshield_method_akismet'); // Generate Instance for Akismet method
			$error .= $method_akismet_instance->checkAkismet($fieldValues, 'ke_userregister');
		}

		// 2a. Safe log file
		if ($error) {
			$method_log_instance = t3lib_div::makeInstance('tx_wtspamshield_log'); // Generate Instance for session method
			$method_log_instance->dbLog('ke_userregister', $error, $fieldValues);
		}

		// 2b. Send email to admin
		if ($error) {
			$method_sendEmail_instance = t3lib_div::makeInstance('tx_wtspamshield_mail'); // Generate Instance for session method
			$method_sendEmail_instance->sendEmail('ke_userregister', $error, $fieldValues);
		}

		return $error;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_ke_userregister.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_ke_userregister.php']);
}

?>
