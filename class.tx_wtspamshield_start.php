<?php
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_wtspamshield_start extends tslib_pibase {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	
	// start function for any extension
	function main() {
	
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/class.tx_wtspamshield_start.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/class.tx_wtspamshield_start.php']);
}
?>