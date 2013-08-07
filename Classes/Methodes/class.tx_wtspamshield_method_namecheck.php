<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Alexander Kellner <Alexander.Kellner@einpraegsam.net>
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
***************************************************************/

require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Methodes/class.tx_wtspamshield_method_abstract.php');

class tx_wtspamshield_method_namecheck extends tx_wtspamshield_method_abstract {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	
	/**
	 * Function nameCheck() to disable the same first- and lastname
	 *
	 * @param	string		$name1: Content of Field Firstname
	 * @param	string		$name2: Content of Field Lastname
	 * @return	string		$error: Return errormessage if error exists
	 */
	function nameCheck($name1, $name2) {
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension
		
		if (isset($conf)) { // Only if Backendconfiguration exists in localconf
			if ($conf['useNameCheck'] == 1) { // Only if enabled in backendconfiguration
				
				if ($name1 === $name2 && $name1) { // if firstname is lastname and firstname exists
					$error = $this->renderCObj($GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['errors.'], 'nameCheck');
				}
				if (isset($error)) {
					return $error;
				}
				
			}
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_namecheck.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_namecheck.php']);
}

?>
