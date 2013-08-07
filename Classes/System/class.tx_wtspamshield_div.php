<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Alexander Kellner <Alexander.Kellner@einpraegsam.net>
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
 * div
 *
 * @author Ralf Zimmermann <ralf.zimmermann@tritum.de>
 * @package tritum
 * @subpackage wt_spamshield
 */
class tx_wtspamshield_div extends tslib_pibase {

	/**
	 * @var string
	 */
	public $extKey = 'wt_spamshield';

	/**
	 * @var mixed
	 */
	public $tsConf;

	/**
	 * @var mixed
	 */
	public $extConf;

	/**
	 * @var tx_wtspamshield_processor
	 */
	public $processor;

	/**
	 * Disable Spamshield for current page - set entry to session
	 *
	 * @param int $time how long should the disabling work (in seconds)
	 * @return void
	 */
	public function disableSpamshieldForCurrentPage($time = '600') {
		$newArray = array(
			'pid' . $GLOBALS['TSFE']->id => (time() + $time)
		);

		$varsFromSession = $GLOBALS['TSFE']->fe_user->getKey('ses', $this->extKey . '_disableSpamshield');
		if (is_array($varsFromSession)) {
			$newArray = array_merge($varsFromSession, $newArray);
		}

		$GLOBALS['TSFE']->fe_user->setKey('ses', $this->extKey . '_disableSpamshield', $newArray);
		$GLOBALS['TSFE']->storeSessionData();
	}

	/**
	 * Check if spamshield function is not disabled via session
	 *
	 * @return boolean
	 */
	public function spamshieldIsNotDisabled() {
		$varsFromSession = $GLOBALS['TSFE']->fe_user->getKey('ses', $this->extKey . '_disableSpamshield');
		if (is_array($varsFromSession)) {
			if (array_key_exists('pid' . $GLOBALS['TSFE']->id, $varsFromSession)) {
				if ($varsFromSession['pid' . $GLOBALS['TSFE']->id] > time()) {
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	/**
	 * isActivated
	 * 
	 * @param string $extension
	 * @return boolean
	 */
	public function isActivated($extension) {
		$tsConf = $this->getTsConf();
		if (!empty($tsConf['enable.'][$extension])
			&& $this->spamshieldIsNotDisabled()
		) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * isActivated
	 * 
	 * @param string $list
	 * @return mixed
	 */
	public function commaListToArray($list) {
		$list = trim($list);
		$list = str_replace(' ', '', $list);
		$list = explode(',', $list);

		return $list;
	}

	/**
	 * getTsConf
	 * 
	 * @return mixed
	 */
	public function getTsConf() {
		if (!isset($this->tsConf)) {
			$this->tsConf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['wt_spamshield.'];
		}
		return $this->tsConf;
	}

	/**
	 * getExtConf
	 *
	 * @return mixed
	 */
	public function getExtConf() {
		if(!isset($this->extConf)) {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
			if ( !is_array($extConf) ) {
				echo $this->msg('Please open ' . $this->extKey . ' in the Extension Manager, scroll down and click "Update"');
				$this->extConf = NULL;
			} else {
				$this->extConf = $extConf;
			}
		}
		return $this->extConf;
	}

	/**
	 * getProcessor
	 * 
	 * @return tx_wtspamshield_processor
	 */
	public function getProcessor() {
		if (!isset($this->processor)) {
			$this->processor = t3lib_div::makeInstance('tx_wtspamshield_processor');
		}
		return $this->processor;
	}

	/**
	 * Returns message with optical flair
	 *
	 * @param string $str Message to show
	 * @param integer $pos Is this a positive message? (0,1,2)
	 * @param boolean $die Process should be died now
	 * @param boolean $prefix Activate or deactivate prefix "$extKey: "
	 * @param string $id id to add to the message
	 *                   (maybe to do some javascript effects)
	 * @return string $string Manipulated string
	 */
	public function msg($str, $pos = 0, $die = 0, $prefix = 1, $id = '') {
			// config
		if ($prefix) {
			$string = $this->extKey . ($pos != 1 && $pos != 2 ? ' Error' : '') . ': ';
		}
		$string .= $str;
		$urlPrefix = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . '/';
		if (t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/' != t3lib_div::getIndpEnv('TYPO3_SITE_URL')) {
			$urlPrefix .= str_replace(
				t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST') . '/',
				'',
				t3lib_div::getIndpEnv('TYPO3_SITE_URL')
			);
		}

			// let's go
		switch ($pos) {
			default:
				$wrap = '<div class="' .
					$this->extKey .
					'_msg_error" style="background-color: #FBB19B; background-position: 4px 4px; background-image: url(' .
					$urlPrefix .
					'typo3/gfx/error.png); background-repeat: no-repeat; padding: 5px 30px; font-weight: bold; border: ' .
					'1px solid #DC4C42; margin-bottom: 20px; font-family: arial, verdana; color: #444; font-size: 12px;"';
				if ($id) {
					$wrap .= ' id="' . $id . '"';
				}
				$wrap .= '>';
				break;

			case 1:
				$wrap = '<div class="' .
					$this->extKey .
					'_msg_status" style="background-color: #CDEACA; background-position: 4px 4px; background-image: url(' .
					$urlPrefix .
					'typo3/gfx/ok.png); background-repeat: no-repeat; padding: 5px 30px; font-weight: bold; border: ' .
					'1px solid #58B548; margin-bottom: 20px; font-family: arial, verdana; color: #444; font-size: 12px;"';
				if ($id) {
					$wrap .= ' id="' . $id . '"';
				}
				$wrap .= '>';
				break;

			case 2:
				$wrap = '<div class="' .
					$this->extKey .
					'_msg_error" style="background-color: #DDEEF9; background-position: 4px 4px; background-image: url(' .
					$urlPrefix . 'typo3/gfx/information.png); background-repeat: no-repeat; padding: 5px 30px; font-weight: ' .
					'bold; border: 1px solid #8AAFC4; margin-bottom: 20px; font-family: arial, verdana; color: #444; font-size: 12px;"';
				if ($id) {
					$wrap .= ' id="' . $id . '"';
				}
				$wrap .= '>';
				break;
		}

		if (!$die) {
			return $wrap . $string . '</div>';
		} else {
			die ($string);
		}
		return '';
	}

}

if (defined('TYPO3_MODE')
	&& isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/System/class.tx_wtspamshield_div.php'])
) {
	require_once ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/System/class.tx_wtspamshield_div.php']);
}

?>