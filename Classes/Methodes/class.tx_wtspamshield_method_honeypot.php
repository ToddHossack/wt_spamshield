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

class tx_wtspamshield_method_honeypot extends tx_wtspamshield_method_abstract {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	var $inputName; // Name for input field
	var $prefix_inputName; // Prefix for input name
	
	/**
	 * Function createHoneypot() creates a non-visible input field
	 *
	 * @return	string		$code: Return form field (honeypot)
	 */
	function createHoneypot() {
		$this->conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension
		if ($this->conf['honeypotCheck']) { // only if honeypotcheck was enabled in ext Manager

			$cObjType = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['explanation'];
			$cObjvalues = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['explanation.'];
			$LLL = $cObjvalues['value'];
			$cObjvalues['value'] = $this->getL10n($LLL);
			$code = $this->cObj->cObjGetSingle($cObjType, $cObjvalues);

			$code .= '<input type="text" name="';
			$code .= $this->prefix_inputName . '[' . $this->inputName . ']"';
			$code .= ' ' . $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['css.']['inputStyle'];
			$code .= ' value=""';
			$code .= ' ' . $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['honeypot.']['css.']['inputClass'];
			$code .= ' />';

			return $code;
		}
	}

	/**
	 * Function checkHoney() checks if a fly is in the honeypot
	 *
	 * @param	array		$sessiondata: Array with submitted values
	 * @return	string		$error: Return errormessage if error exists
	 */
	function checkHoney(&$sessiondata) {
		$this->conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension

		if (!empty($sessiondata[$this->inputName]) && $this->conf['honeypotCheck']) { // There is spam in the honeypot AND honeypotcheck was enabled in ext Manager
			return $this->renderCObj($GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['errors.'], 'honeypot');
		}

		unset($sessiondata[$this->inputName]); // delete honeypot
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_honeypot.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_honeypot.php']);
}

?>
