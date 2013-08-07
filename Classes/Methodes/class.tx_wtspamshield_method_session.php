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

/**
 * session check
 *
 * @author Ralf Zimmermann <ralf.zimmermann@tritum.de>
 * @package tritum
 * @subpackage wt_spamshield
 */
class tx_wtspamshield_method_session extends tx_wtspamshield_method_abstract {

	/**
	 * @var mixed
	 */
	public $fieldValues;

	/**
	 * @var mixed
	 */
	public $additionalValues;

	/**
	 * @var string
	 */
	public $tsKey;

	/**
	 * Set Timestamp in session (when the form is rendered)
	 *
	 * @param boolean $forceValue Whether to force setting the
	 *                            timestampe in the session
	 * @return void
	 */
	public function setSessionTime($forceValue = TRUE) {
		$extConf = $this->getDiv()->getExtConf();

		if (isset($extConf)) {
			$timeStamp = intval($GLOBALS['TSFE']->fe_user->getKey('ses', 'wt_spamshield_form_tstamp'));
			$isOutdated = ($timeStamp + $extConf['SessionEndTime'] < time());

			if ($forceValue || $isOutdated) {
				$GLOBALS['TSFE']->fe_user->setKey('ses', 'wt_spamshield_form_tstamp', time());
				$GLOBALS['TSFE']->storeSessionData();
			}
		}
	}

	/**
	 * Return Errormessage if session it runned out
	 * 
	 * @return string $error Return errormessage if error exists
	 */
	public function validate() {
		$extConf = $this->getDiv()->getExtConf();
		$error = '';

		if (isset($extConf)) {
			if ($extConf['useSessionCheck'] == 1) {
				$sessTstamp = intval($GLOBALS['TSFE']->fe_user->getKey('ses', 'wt_spamshield_form_tstamp'));
				$tsConf = $this->getDiv()->getTsConf();

				if ($sessTstamp > 0) {
					if ((($sessTstamp + $extConf['SessionEndTime']) < time()) && ($extConf['SessionEndTime'] > 0)) {
						$error = $this->renderCobj($tsConf['errors.'], 'session_error_1');
					} elseif ( (($sessTstamp + $extConf['SessionStartTime']) > time())
								&& ($extConf['SessionStartTime'] > 0)
					) {
						$error = $this->renderCobj($tsConf['errors.'], 'session_error_2');
					}
				} else {
					$error = $this->renderCobj($tsConf['errors.'], 'session_error_3');
				}
			}
		}

		return $error;
	}

}

if (defined('TYPO3_MODE')
	&& isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_session.php'])
) {
	require_once ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_session.php']);
}

?>