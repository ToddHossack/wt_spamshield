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

class tx_wtspamshield_ve_guestbook extends tslib_pibase {

	var $prefix_inputName = 'tx_veguestbook_pi1'; 

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
	 * @param	array		$row: Values from database
	 * @param	array		$config: configuration
	 * @param	object		$obj: parent object
	 * @return	array		$markerArray
	 */
	function extraItemMarkerProcessor(&$markerArray, $row, $config, &$obj) {
		
		if ( // If guestbookform is shown AND if spamshield should be activated
			$obj->code == 'FORM' && 
			$this->getAbstract()->isActivated('ve_guestbook')
		) {
			// 1. check Extension Manager configuration
			$this->getAbstract()->getDiv()->checkConf(); // Check Extension Manager configuration
			
			// 2. Session check - generate session entry
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$method_session_instance->setSessionTime(); // Start setSessionTime() Function: Set session if form is loaded
			
			// 3. Honeypot check - generate honeypot Input field
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['ve_guestbook'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot'); // Generate Instance for honeypot method
			$method_honeypot_instance->inputName = $honeypot_inputName; 
			$method_honeypot_instance->prefix_inputName = $this->prefix_inputName; 
			$obj->templateCode = str_replace('</form>', $method_honeypot_instance->createHoneypot() . '</form>', $obj->templateCode); // add input field
		}
		return $markerArray; // return markerArray to ve_guestbook (without change)
	} 
	
	/**
	 * Function preEntryInsertProcessor is called from a guestbook hook and gives the possibility to disable the db entry of the GB
	 *
	 * @param	array		$saveData: Values to save
	 * @param	object		$obj: parent object
	 * @return	array		$saveData
	 */
	function preEntryInsertProcessor($saveData, &$obj) {
		global $TSFE;
		$cObj = $TSFE->cObj; // cObject
		$error = ''; // no error at the beginning

		if ( $this->getAbstract()->isActivated('ve_guestbook') ) {
			// get GPvars, downwards compatibility
			$T3Version = class_exists('t3lib_utility_VersionNumber')
				? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version)
				: t3lib_div::int_from_ver(TYPO3_version);
			if ($T3Version < 4006000) {
				$validateArray = t3lib_div::GPvar('tx_veguestbook_pi1');
			} else {
				$validateArray = t3lib_div::_GP('tx_veguestbook_pi1');
			}
			$error = $this->processValidationChain($validateArray);
			
			// 2c. Truncate ve_guestbook temp table
			if ($error) {
				mysql_query('TRUNCATE TABLE tx_wtspamshield_veguestbooktemp'); // Truncate ve_guestbook temp table
			}
			
			// 2d. Redirect if error happens
			if (!empty($error)) { // If error
				$saveData = array('tstamp' => time()); // add timestamp
				$obj->strEntryTable = 'tx_wtspamshield_veguestbooktemp'; // change table for saving
				$obj->config['notify_mail'] = ''; // don't send a notify email
				$obj->config['feedback_mail'] = false; // don't send a feedback mail
				$obj->config['redirect_page'] = (intval($GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['redirect.']['ve_guestbook']) > 0 ? $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['redirect.']['ve_guestbook'] : 1); // pid to redirect
				unset($obj->tt_news); // remove superfluous tt_news piVars
			}
		}
		return $saveData; // should always return something or error will happen
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

		// 1b. nameCheck
		if (!$error) {
			$method_namecheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_namecheck'); // Generate Instance for namecheck method
			$error .= $method_namecheck_instance->nameCheck($fieldValues['firstname'], $fieldValues['surname']);
		}
		
		// 1c. httpCheck
		if (!$error) {
			$method_httpcheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_httpcheck'); // Generate Instance for http method
			$error .= $method_httpcheck_instance->httpCheck($fieldValues);
		}
		
		// 1d. sessionCheck
		if (!$error) {
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$error .= $method_session_instance->checkSessionTime();
		}
		
		// 1e. honeypotCheck
		if (!$error) {
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['ve_guestbook'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot'); // Generate Instance for honeypot method
			$method_honeypot_instance->inputName = $honeypot_inputName; // name for input field
			$error .= $method_honeypot_instance->checkHoney($fieldValues);
		}
		
		// 1f. Akismet Check
		if (!$error) {
			$method_akismet_instance = t3lib_div::makeInstance('tx_wtspamshield_method_akismet'); // Generate Instance for Akismet method
			$error .= $method_akismet_instance->checkAkismet($fieldValues, 've_guestbook');
		}
		
		// 2a. Safe log file
		if ($error) {
			$method_log_instance = t3lib_div::makeInstance('tx_wtspamshield_log'); // Generate Instance for session method
			$method_log_instance->dbLog('ve_guestbook', $error, $fieldValues);
		}
		
		// 2b. Send email to admin
		if ($error) {
			$method_sendEmail_instance = t3lib_div::makeInstance('tx_wtspamshield_mail'); // Generate Instance for session method
			$method_sendEmail_instance->sendEmail('ve_guestbook', $error, $fieldValues);
		}
		
		return $error;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_ve_guestbook.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_ve_guestbook.php']);
}

?>
