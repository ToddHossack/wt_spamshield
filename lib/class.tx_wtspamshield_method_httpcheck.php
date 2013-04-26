<?php
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_wtspamshield_method_httpcheck extends tslib_pibase {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	var $searchstring = 'http://'; // searchstring
	
	// Function nameCheck() to disable the same first- and lastname
	function httpCheck($array) {
		$this->conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension
		
		if(isset($this->conf) && isset($array)) { // Only if Backendconfiguration exists in localconf
			if($this->conf['usehttpCheck'] > 0) { // Only if enabled in backendconfiguration (disabled if 0)
				
				$no_of_errors = 0; // init $errors
				$error = 'It\'s not allowed to use more than '.$this->conf['usehttpCheck'].' links within this form<br />';
					
				foreach ($array as $value) { // One loop for every array entry
					$result = ''; // init $result
					preg_match_all('@'.$this->searchstring.'@', $value, $result); // give me all http:// of current string
					if(isset($result[0])) $no_of_errors += count($result[0]); // add numbers of http:// to $errors
				}
				
				if($no_of_errors > $this->conf['usehttpCheck']) return $error; // return message if more than allowed http enters
				
			}
		}
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/lib/class.tx_wtspamshield_method_httpcheck.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/lib/class.tx_wtspamshield_method_httpcheck.php']);
}
?>