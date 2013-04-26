<?php
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_wtspamshield_log extends tslib_pibase {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	var $dbInsert = 1; // DB insert can be disabled for testing
	
	// start function for any extension
	function dbLog($ext,$error,$formArray) {
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension
		
		if(isset($conf)) { // Only if Backendconfiguration exists in localconf
			if($conf['pid'] > -1) { // Only if enabled in backendconfiguration and key was set
				
				// DB entry for table tx_wtspamshield_log
				$db_values = array (
					'pid' => $conf['pid'],
					'tstamp' => time(),
					'crdate' => time(),
					'form' => $ext,
					'errormsg' => str_replace(array('<br>','<br />'),"\n",$error),
					'formvalues' => t3lib_div::view_array($formArray),
					'pageid' => $GLOBALS['TSFE']->id,
					'ip' => t3lib_div::getIndpEnv('REMOTE_ADDR'),
					'useragent' => t3lib_div::getIndpEnv('HTTP_USER_AGENT')
				);
				if($this->dbInsert) $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_wtspamshield_log', $db_values); // DB entry
				
			}
		}
			
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/functions/class.tx_wtspamshield_log.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/functions/class.tx_wtspamshield_log.php']);
}
?>