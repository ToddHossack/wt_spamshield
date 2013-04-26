<?php
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_wtspamshield_method_session extends tslib_pibase {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	
	// Set Session Time
	function setSessionTime() {
		$GLOBALS['TSFE']->fe_user->setKey('ses','wt_spamshield_form_tstamp',time()); // write timestamp to session
		$GLOBALS['TSFE']->storeSessionData(); // store session
	}
	
	// Stop DB entry if spam
	function checkSessionTime($note1, $note2, $note3) {
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension
		
		if(isset($conf)) { // Only if Backendconfiguration exists in localconf
			if($conf['useSessionCheck'] == 1) { // Only if enabled in backendconfiguration
				$sess_tstamp = intval($GLOBALS['TSFE']->fe_user->getKey('ses','wt_spamshield_form_tstamp')); // Get timestamp from session
				
				if($sess_tstamp > 0) { // If there is a timestamp
					if((($sess_tstamp + $conf['SessionEndTime']) < time()) && ($conf['SessionEndTime'] > 0)) { // If it's to last
						$error = 'Sorry, but you have waited too long to send form values<br />'; // default
						if ($note1) $error = $note1.'<br />'; // value from tsconfig
					} 
					elseif((($sess_tstamp + $conf['SessionStartTime']) > time()) && ($conf['SessionStartTime'] > 0)) { // If it's to fast
						$error = 'Please wait some seconds before sending form<br />'; // default
						if ($note2) $error = $note2.'<br />'; // value from tsconfig
					}
				} else {
					$error = 'No tstamp set in session<br />'; // default
					if ($note3) $error = $note3.'<br />'; // value from tsconfig
				}
				
			}
		} else $error = 'Please update your extension ('.$this->extKey.') in the Extension Manager<br />'; // No conf, so update ext in ext manager
			
		return $error;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/lib/class.tx_wtspamshield_method_session.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/lib/class.tx_wtspamshield_method_session.php']);
}
?>