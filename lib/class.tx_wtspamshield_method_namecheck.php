<?php
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_wtspamshield_method_namecheck extends tslib_pibase {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	
	// Function nameCheck() to disable the same first- and lastname
	function nameCheck($name1,$name2) {
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension
		
		if(isset($conf)) { // Only if Backendconfiguration exists in localconf
			if($conf['useNameCheck'] == 1) { // Only if enabled in backendconfiguration
				
				if($name1 === $name2 && $name1) { // if firstname is lastname and firstname exists
					$error = 'It\'s not allowed to use the same firstname and lastname<br />';
				}
				if(isset($error)) return $error;
				
			}
		}
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/lib/class.tx_wtspamshield_method_namecheck.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/lib/class.tx_wtspamshield_method_namecheck.php']);
}
?>