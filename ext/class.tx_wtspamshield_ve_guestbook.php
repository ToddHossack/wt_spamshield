<?php
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_namecheck.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_httpcheck.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_session.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'lib/class.tx_wtspamshield_method_akismet.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'functions/class.tx_wtspamshield_log.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'functions/class.tx_wtspamshield_mail.php');

class tx_wtspamshield_ve_guestbook extends tslib_pibase {

	// Function for ve_guestbook form: set tstamp in session
	function extraItemMarkerProcessor($markerArray, $row, $config, $all) {
		if($all->code == 'FORM') { // If guestbookform is shown
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$method_session_instance->setSessionTime(); // Start setSessionTime() Function: Set session if form is loaded
		}
		return $markerArray; // return markerArray to ve_guestbook (without change)
	}
	
	// Stop DB entry if spam - use Hook in ve_guestbook
	function formvalidation($form) {
		$error = ''; // no error at the beginning
		$this->messages = $GLOBALS['TSFE']->tmpl->setup['wt_spamshield.']['message.']; // get messages from Backend
		
		// 1a. nameCheck
		if(!$error) {
			$method_namecheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_namecheck'); // Generate Instance for session method
			$error .= $method_namecheck_instance->nameCheck($form['firstname'], $form['surname'], $this->messages['namecheck']);
		}
		
		// 1b. httpCheck
		if(!$error) {
			$method_httpcheck_instance = t3lib_div::makeInstance('tx_wtspamshield_method_httpcheck'); // Generate Instance for session method
			$error .= $method_httpcheck_instance->httpCheck($form, $this->messages['httpcheck']);
		}
		
		// 1c. sessionCheck
		if(!$error) {
			$method_session_instance = t3lib_div::makeInstance('tx_wtspamshield_method_session'); // Generate Instance for session method
			$error .= $method_session_instance->checkSessionTime($this->messages['session.']['note1'], $this->messages['session.']['note2'], $this->messages['session.']['note3']);
		}
		
		// 1d. Akismet Check
		if(!$error) {
			$method_akismet_instance = t3lib_div::makeInstance('tx_wtspamshield_method_akismet'); // Generate Instance for session method
			$error .= $method_akismet_instance->checkAkismet($form, $this->messages['akismet']);
		}
		
		// 2a. Safe log file
		if($error) {
			$method_log_instance = t3lib_div::makeInstance('tx_wtspamshield_log'); // Generate Instance for session method
			$method_log_instance->dbLog('ve_guestbook',$error,$form);
		}
		
		// 2b. Send email to admin
		if($error) {
			$method_sendEmail_instance = t3lib_div::makeInstance('tx_wtspamshield_mail'); // Generate Instance for session method
			$method_sendEmail_instance->sendEmail('ve_guestbook',$error,$form);
		}
		
		// 2c. Return Error message if exists
		if(!empty($error)) { // If error
			return $error;
		}
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/ext/class.tx_wtspamshield_ve_guestbook.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/ext/class.tx_wtspamshield_ve_guestbook.php']);
}
?>