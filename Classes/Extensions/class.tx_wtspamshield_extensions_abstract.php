<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Ralf Zimmermann <Ralf.Zimmermann@tritum.de>
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

require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/System/class.tx_wtspamshield_log.php');
require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/System/class.tx_wtspamshield_div.php');
require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/System/class.tx_wtspamshield_mail.php');

require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Methodes/class.tx_wtspamshield_method_blacklist.php');
require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Methodes/class.tx_wtspamshield_method_namecheck.php');
require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Methodes/class.tx_wtspamshield_method_httpcheck.php');
require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Methodes/class.tx_wtspamshield_method_session.php');
require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Methodes/class.tx_wtspamshield_method_akismet.php');
require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Methodes/class.tx_wtspamshield_method_honeypot.php');
require_once(t3lib_extMgm::extPath('wt_spamshield') . 'Classes/Methodes/class.tx_wtspamshield_method_unique.php');

if(t3lib_extMgm::isLoaded('direct_mail_subscription')) {
	require_once(t3lib_extMgm::extPath('direct_mail_subscription') . 'fe_adminLib.inc');
}


class tx_wtspamshield_extensions_abstract {

	/**
	 * @var tx_wtspamshield_div
	 */
	protected $div;

	/**
	 * @var mixed
	 */
	protected $configuration;

	/**
	 * @return tx_wtspamshield_div
	 */
	public function getDiv() {
		if (!isset($this->div)) {
			$this->div = t3lib_div::makeInstance('tx_wtspamshield_div');
		}
		return $this->div;
	}

	/**
	 * @return mixed
	 */
	public function getConfiguration() {
		if (!isset($this->configuration)) {
			$this->configuration = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.'];
		}
		return $this->configuration;
	}

	/**
	 * @param string $extension
	 * @return boolean
	 */
	public function isActivated($extension) {
		$configuration = $this->getConfiguration();
		if(	!empty($configuration['enable.'][$extension]) &&
			$this->getDiv()->spamshieldIsNotDisabled()
		) {
			return true;
		}
		return false;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_extensions_abstract.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Extensions/class.tx_wtspamshield_extensions_abstract.php']);
}

?>
