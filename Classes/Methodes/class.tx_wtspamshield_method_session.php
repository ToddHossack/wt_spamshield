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

class tx_wtspamshield_method_session extends tx_wtspamshield_method_abstract {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	
	/**
	 * Set Timestamp in session (when the form is rendered)
	 *
	 * @param boolean $forceValue Whether to force setting the timestampe in the session
	 * @return	void
	 */
	function setSessionTime($forceValue = TRUE) {
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension

		$timeStamp = intval($GLOBALS['TSFE']->fe_user->getKey('ses', 'wt_spamshield_form_tstamp'));
		$isOutdated = ($timeStamp + $conf['SessionEndTime'] < time());

		if ($forceValue || $isOutdated) {
			$GLOBALS['TSFE']->fe_user->setKey('ses', 'wt_spamshield_form_tstamp', time()); // write timestamp to session
			$GLOBALS['TSFE']->storeSessionData(); // store session
		}
	}
	
	/**
	 * Return Errormessage if session it runned out
	 * @return	string		$error: Return errormessage if error exists
	 */
	function checkSessionTime() {
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension
		
		if (isset($conf)) { // Only if Backend configuration exists in localconf
			if ($conf['useSessionCheck'] == 1) { // Only if enabled in backend configuration
				$sess_tstamp = intval($GLOBALS['TSFE']->fe_user->getKey('ses', 'wt_spamshield_form_tstamp')); // Get timestamp from session
				
				if ($sess_tstamp > 0) { // If there is a timestamp
					if ((($sess_tstamp + $conf['SessionEndTime']) < time()) && ($conf['SessionEndTime'] > 0)) { // If it's too slow
						$error = $this->renderCObj($GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['errors.'], 'session_error_1');
					} 
					elseif ((($sess_tstamp + $conf['SessionStartTime']) > time()) && ($conf['SessionStartTime'] > 0)) { // If it's too fast
						$error = $this->renderCObj($GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['errors.'], 'session_error_2');
					}
				} else {
					$error = $this->renderCObj($GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.']['errors.'], 'session_error_3');
				}
				
			}
		} else {
			$error = 'Please update your extension (' . $this->extKey . ') in the Extension Manager<br />'; // No conf, so update ext in EM
		}
			
		return $error;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_session.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_session.php']);
}

?>
