<?php
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_wtspamshield_method_unique extends tslib_pibase {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	
	// Stop DB entry if spam
	function main($sessiondata, $note) {
		$this->conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension
		$found = 0; // no errors at the beginning
		$wholearray = array(); // clear array

		if ($this->conf['notUnique']) { // only if there are values in the backend
			$error = 'It\'s not allowed to use same entries in differnt fields<br />'; // default
			if ($note) $error = $note.'<br />'; // get message from tsconfig
			$fieldarray = t3lib_div::trimExplode(',', $this->conf['notUnique'], 1); // explode at ','
			
			if (is_array($fieldarray)) { // if there is an array
				foreach ($fieldarray as $key => $value) { // one loop for every field
					if ($sessiondata[$value]) $wholearray[] = $sessiondata[$value]; // if value exists in session, write value to an array
				}
			}

			if (count($wholearray) != count(array_unique($wholearray))) { // if numbers of array values not numbers if array values without double entries
				$found = 1; // found spam
			}
		}
		
		if ($found) return $error;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/lib/class.tx_wtspamshield_method_unique.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/lib/class.tx_wtspamshield_method_unique.php']);
}
?>