<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Ralf Zimmermann <ralf.zimmermann@tritum.de>
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
*/

require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Extensions/class.tx_wtspamshield_extensions_abstract.php');

class user_tx_wtspamshield_direct_mail_subscription extends user_feAdmin {

	var $prefix_inputName = 'FE[tt_address]'; 
	var $spamshieldDisplayError;

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

	function displayCreateScreen() {

		if ( $this->getAbstract()->isActivated('direct_mail_subscription') ) {
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['direct_mail_subscription'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot');
			$method_honeypot_instance->inputName = $honeypot_inputName;
			$method_honeypot_instance->prefix_inputName = $this->prefix_inputName;
			$this->markerArray['###HIDDENFIELDS###'] .= $method_honeypot_instance->createHoneypot();
			if($this->spamshieldDisplayError) {
				$this->markerArray['###HIDDENFIELDS###'] .= $this->spamshieldDisplayError;
			}
		}

		return parent::displayCreateScreen();
	}

	function save() {
		// config
		$error = ''; // no error at the beginning

		if ( $this->getAbstract()->isActivated('direct_mail_subscription') ) {
			$validateArray = $this->dataArr;
			$error = $this->processValidationChain($validateArray);

			if (!empty($error)) {
				// error handling
				$method_log_instance = t3lib_div::makeInstance('tx_wtspamshield_log'); // Generate Instance for logging method
				$method_log_instance->dbLog('direct_mail_subscription', $error, $validateArray);
				//$this->error='###TEMPLATE_NO_PERMISSIONS###';
				$this->saved=0;
				$this->cmd='create';
				$this->spamshieldDisplayError = $error;
			} else {
				return parent::save();
			}
		} else {
			return parent::save();
		}
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
			$honeypot_inputName = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['inputname.']['direct_mail_subscription'];
			$method_honeypot_instance = t3lib_div::makeInstance('tx_wtspamshield_method_honeypot'); // Generate Instance for honeypot method
			$method_honeypot_instance->inputName = $honeypot_inputName; // name for input field
			$error .= $method_honeypot_instance->checkHoney($fieldValues);
		}
		
		return $error;
	}
}

?>
