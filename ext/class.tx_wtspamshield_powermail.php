<?php
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_session.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_httpcheck.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_namecheck.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_akismet.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_unique.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'functions/class.tx_wtspamshield_log.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'functions/class.tx_wtspamshield_mail.php');

class tx_wtspamshield_powermail extends tslib_pibase {

	// Function PM_FormWrapMarkerHook() to manipulate whole formwrap
	function PM_FormWrapMarkerHook($obj) {
		$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
		$method_session_instance->setSessionTime(); // Start setSessionTime() Function: Set session if form is loaded
	}

	// Function PM_FieldWrapMarkerHook() to manipulate Fieldwraps
	function PM_SubmitBeforeMarkerHook($obj,$markerArray = array(),$sessiondata = array()) {
		// config
		$error = ''; // no error at the beginning
		$this->messages = $GLOBALS['TSFE']->tmpl->setup['wt_spamshield.']['message.']; // Get messages from TS
		
		// 1a. sessionCheck
		if (!$error) {
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$error .= $method_session_instance->checkSessionTime($this->messages['session.']['note1'], $this->messages['session.']['note2'], $this->messages['session.']['note3']);
		}
		
		// 1b. httpCheck
		if (!$error) {
			$method_httpcheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_httpcheck'); // Generate Instance for session method
			$error .= $method_httpcheck_instance->httpCheck($sessiondata, $this->messages['httpcheck']);
		}
		
		// 1c. uniqueCheck
		if (!$error) {
			$method_unique_instance = t3lib_div::makeInstance('tx_wtspamshield_method_unique'); // Generate Instance for session method
			$error .= $method_unique_instance->main($sessiondata, $this->messages['uniquecheck']);
		}
		
		// 2a. Safe log file
		if ($error) {
			$method_log_instance = t3lib_div::makeInstance('tx_wtspamshield_log'); // Generate Instance for session method
			$method_log_instance->dbLog('powermail',$error,$sessiondata);
		}
		
		// 2b. Send email to admin
		if ($error) {
			$method_sendEmail_instance = t3lib_div::makeInstance('tx_wtspamshield_mail'); // Generate Instance for session method
			$method_sendEmail_instance->sendEmail('powermail',$error,$sessiondata);
		}
		
		// 2c. Return Error message if exists
		if (!empty($error)) { // If error
			return $error;
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/ext/class.tx_wtspamshield_powermail.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/ext/class.tx_wtspamshield_powermail.php']);
}
?>