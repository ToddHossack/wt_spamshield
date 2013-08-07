<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Lina Wolf <2010@lotypo3.de>
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

/**
* Implementation of Hook from tx_comments to make the wt_spamshield work
* @author Lina Wolf <2010@lotypo3.de>
*/

/*
 * @author Ralf Zimmermann <ralf.zimmermann@tritum.de
 */

require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Extensions/class.tx_wtspamshield_extensions_abstract.php');

class tx_wtspamshield_comments extends tslib_pibase {

	var $prefix_inputName = 'tx_comments_pi1';
	var $points;

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
	* Implementation of Hook "form" from tx_comments (when the form is rendered)
	* Adds the Honeypot input field to the marker ###JS_USER_DATA### (part of the default template)
	* @param params array of 'pObject' => Name of extension 'markers' array of markers 'template' the template
	* @param pObj 
	* @returns the changed marker array
	*/
	function form($params, $pObj) {
		$template = $params['template'];
		$markers = $params['markers'];
		
		if ( $this->getAbstract()->isActivated('comments') ) {
			
			// 1. check Extension Manager configuration
			$this->getAbstract()->getDiv()->checkConf(); // Check Extension Manager configuration
			
			// 2. Session check - generate session entry
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$method_session_instance->setSessionTime(); // Start setSessionTime() Function: Set session if form is loaded
			
			// 3. Honeypot check - generate honeypot Input field
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['comments'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot'); // Generate Instance for honeypot method
			$method_honeypot_instance->inputName = $honeypot_inputName; 
			$method_honeypot_instance->prefix_inputName = $this->prefix_inputName; 
			$markers['###JS_USER_DATA###'] = $method_honeypot_instance->createHoneypot() . $markers['###JS_USER_DATA###'];	
		}
		return $markers;
	}
	
	/**
	* Implementation of Hook "externalSpamCheck" from tx_comments 
	* Test for spam and addd 1000 spampoints for each Problem found
	* @param params array of 'pObject' => Name of extension 'form' array of fields in the form 'points' excistent spam points
	* @param pObj 
	* @returns number of spam points increased by 100 for every problem that was found
	*/
	function externalSpamCheck($params, $pObj) {
		global $TSFE;
		$cObj = $TSFE->cObj; // cObject
		$error = ''; // no error at the beginning
		$validateArray = $params['formdata'];
		$this->points = $params['points'];

		if ( $this->getAbstract()->isActivated('comments') ) {
			$error = $this->processValidationChain($validateArray);
		}
		
		return $this->points; 
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

			if (!empty($tempError)) {
				$this->points += 1000;
			}
			$error .= $tempError;
		}
		
		// 1b. nameCheck
		if (!$error) {
			$method_namecheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_namecheck'); // Generate Instance for namecheck method
			$tempError = $method_namecheck_instance->nameCheck($fieldValues['firstname'], $fieldValues['lastname']);
			
			if (!empty($tempError)) {
				$this->points += 1000;
			}
			$error .= $tempError;
		}
		
		// 1c. httpCheck
		if (!$error) {
			$method_httpcheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_httpcheck'); // Generate Instance for http method
			$tempError = $method_httpcheck_instance->httpCheck($fieldValues);
			
			if (!empty($tempError)) {
				$this->points += 1000;
			}
			$error .= $tempError;
		}
		
		// 1d. sessionCheck
		if (!$error) {
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$tempError = $method_session_instance->checkSessionTime();
			
			if (!empty($tempError)) {
				$this->points += 1000;
			}
			$error .= $tempError;
		}
		
		
		// 1e. honeypotCheck
		if (!$error) {
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['comments'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot'); // Generate Instance for honeypot method
			$method_honeypot_instance->inputName = $honeypot_inputName; // name for input field
			$tempError = $method_honeypot_instance->checkHoney($fieldValues);
			
			if (!empty($tempError)) {
				$this->points += 1000;
			}
			$error .= $tempError;
		}
		
		
		// 1f. Akismet Check
		if (!$error) {
			$method_akismet_instance = t3lib_div::makeInstance('tx_wtspamshield_method_akismet'); // Generate Instance for Akismet method
			$tempError =  $method_akismet_instance->checkAkismet($fieldValues, 'comments');
			
			if (!empty($tempError)) {
				$this->points += 1000;
			}
			$error .= $tempError;
		}
		
		
		// 2a. Safe log file
		if ($error) {
			$method_log_instance = t3lib_div::makeInstance('tx_wtspamshield_log'); // Generate Instance for session method
			$method_log_instance->dbLog('comments', $error, $fieldValues);
		}
		
		// 2b. Send email to admin
		if ($error) {
			$method_sendEmail_instance = t3lib_div::makeInstance('tx_wtspamshield_mail'); // Generate Instance for session method
			$method_sendEmail_instance->sendEmail('comments', $error, $fieldValues);
		}
		
		return $error;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_comments.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_comments.php']);
}

?>
