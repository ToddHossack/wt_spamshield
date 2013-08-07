<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Ralf Zimmermann <ralf.zimmermann@tritum.de>
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

require_once(PATH_tslib . 'class.tslib_pibase.php');

class tx_wtspamshield_method_abstract extends tslib_pibase {

	var $LL = '';
	var $L = false;
	var $cObj;

	function __construct() {
		if($GLOBALS['LANG']->lang)
			$this->L = $GLOBALS['LANG'];
		else
			$this->L = $GLOBALS['TSFE'];

		$this->LL = $this->includeLocalLang();
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
	}

	function getL10n($string) {
		$message = $this->L->getLLL($string, $this->LL);
		return $message;
	}

	function includeLocalLang() {
		$llFile = t3lib_extMgm::extPath('wt_spamshield') . 'Resources/Private/Language/locallang.xml';
		$T3Version = class_exists('t3lib_utility_VersionNumber')
			? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version)
			: t3lib_div::int_from_ver(TYPO3_version);
		if ($T3Version < 4006000) {
			$xmlParser = t3lib_div::makeInstance('t3lib_l10n_parser_Llxml');
			$LOCAL_LANG = $xmlParser->getParsedData($llFile, $this->L->lang);
		} else {
			$LOCAL_LANG = t3lib_div::readLLXMLfile($llFile, $this->L->lang);
		}
		return $LOCAL_LANG;
	}

	function renderCObj($section, $key) {
		$cObjType = $section[$key];
		$cObjvalues = $section[$key.'.'];
		$LLL = $cObjvalues['value'];
		$cObjvalues['value'] = $this->getL10n($LLL);
		$content = $this->cObj->cObjGetSingle($cObjType, $cObjvalues);
		return $content;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_abstract.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_abstract.php']);
}

?>
