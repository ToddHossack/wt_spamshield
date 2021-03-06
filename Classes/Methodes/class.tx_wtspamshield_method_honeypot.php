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
 * honeypod check
 *
 * @author Ralf Zimmermann <ralf.zimmermann@tritum.de>
 * @package tritum
 * @subpackage wt_spamshield
 */
class tx_wtspamshield_method_honeypot extends tx_wtspamshield_method_abstract {

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
	 * Function createHoneypot() creates a non-visible input field
	 *
	 * @return string $code Return form field (honeypot)
	 */
	public function createHoneypot() {
		$extConf = $this->getDiv()->getExtConf();

		if(isset($extConf)) {
			if ($extConf['honeypotCheck']) {
				$tsConf = $this->getDiv()->getTsConf();
				$cObjType = $tsConf['honeypot.']['explanation'];
				$cObjvalues = $tsConf['honeypot.']['explanation.'];
				$lll = $cObjvalues['value'];
				$cObjvalues['value'] = $this->getL10n($lll);
				$code = $this->cObj->cObjGetSingle($cObjType, $cObjvalues);

				$code .= '<input type="text" name="';
				$code .= $this->additionalValues['prefixInputName'] . '[' . $this->additionalValues['honeypotInputName'] . ']"';
				$code .= ' ' . $tsConf['honeypot.']['css.']['inputStyle'];
				$code .= ' value=""';
				$code .= ' ' . $tsConf['honeypot.']['css.']['inputClass'];
				$code .= ' />';

				return $code;
			}
		}
		return '';
	}

	/**
	 * Function validate() checks if a fly is in the honeypot
	 *
	 * @return string $error Return errormessage if error exists
	 */
	public function validate() {
		$extConf = $this->getDiv()->getExtConf();

		if (!empty($this->fieldValues[ $this->additionalValues['honeypotInputName'] ])
			&& isset($extConf)
			&& $extConf['honeypotCheck']
		) {
			$tsConf = $this->getDiv()->getTsConf();
			return $this->renderCobj($tsConf['errors.'], 'honeypot');
		}
		unset($this->fieldValues[ $this->additionalValues['honeypotInputName'] ]);

		return '';
	}
}

if (defined('TYPO3_MODE')
	&& isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_honeypot.php'])
) {
	require_once ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/Methodes/class.tx_wtspamshield_method_honeypot.php']);
}

?>