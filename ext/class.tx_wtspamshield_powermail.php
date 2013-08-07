<?php
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_session.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_httpcheck.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_namecheck.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_akismet.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'functions/class.tx_wtspamshield_log.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'functions/class.tx_wtspamshield_mail.php');

class tx_wtspamshield_powermail extends tslib_pibase {

	// Function PM_FormWrapMarkerHook() to manipulate whole formwrap
	function PM_FormWrapMarkerHook($obj) {
		$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
		$method_session_instance->setSessionTime(); // Start setSessionTime() Function: Set session if form is loaded
		//echo $method_session_instance->checkSessionTime(); // check
	}

	// Function PM_FieldWrapMarkerHook() to manipulate Fieldwraps
	function PM_SubmitBeforeMarkerHook($obj) {
		$error = ''; // no error at the beginning
		
		// 1. sessionCheck
		if(!$error) {
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$error .= $method_session_instance->checkSessionTime();
		}
		
		// 2. httpCheck
		if(!$error) {
			$method_httpcheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_httpcheck'); // Generate Instance for session method
			$error .= $method_httpcheck_instance->httpCheck($obj->pibase->piVars);
		}
		
		// 3. Safe log file
		if($error) {
			$method_log_instance = t3lib_div::makeInstance('tx_wtspamshield_log'); // Generate Instance for session method
			$method_log_instance->dbLog('powermail',$error,$obj->pibase->piVars);
		}
		
		// 4. Send email to admin
		if($error) {
			$method_sendEmail_instance = t3lib_div::makeInstance('tx_wtspamshield_mail'); // Generate Instance for session method
			$method_sendEmail_instance->sendEmail('powermail',$error,$obj->pibase->piVars);
		}
		
		// 5. Return Error message if exists
		if(!empty($error)) { // If error
			return $error;
		}
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/ext/class.tx_wtspamshield_powermail.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/ext/class.tx_wtspamshield_powermail.php']);
}
?>