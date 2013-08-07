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
 * mail
 *
 * @author Ralf Zimmermann <ralf.zimmermann@tritum.de>
 * @package tritum
 * @subpackage wt_spamshield
 */
class tx_wtspamshield_mail extends tslib_pibase {

	/**
	 * @var string
	 */
	public $extKey = 'wt_spamshield';

	/**
	 * @var integer
	 */
	public $sendEmail = 1;

	/**
	 * Function sendEmail sends a notify mail to the admin if spam was recognized
	 * 
	 * @param string $ext Name of extension in which the spam was recognized
	 * @param integer $points 
	 * @param string $errorMessages Error Message
	 * @param array $formArray Array with submitted values
	 * @param boolean $sendPlain Plain instead of HTML mails
	 * @return void
	 */
	public function sendEmail($ext, $points, $errorMessages, $formArray, $sendPlain = 1) {
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);

		$t3Version = class_exists('t3lib_utility_VersionNumber')
			? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version)
			: t3lib_div::int_from_ver(TYPO3_version);

		if (isset($conf)) {
			$errorMessages['points'] = 'Score: ' . $points;
			$errorMessages = strip_tags(implode(' / ', $errorMessages));

			if (t3lib_div::validEmail($conf['email_notify'])) {
				if (!$sendPlain) {
						// Prepare mail
					$mailtext = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
						<html>
							<head>
							</head>
							<body>
								<table>
									<tr>
										<td><strong>Extension:</strong></td>
										<td>' . $ext . '</td>
									</tr>
									<tr>
										<td><strong>PID:</strong></td>
										<td>' . $GLOBALS['TSFE']->id . '</td>
									</tr>
									<tr>
										<td><strong>URL:</strong></td>
										<td>' . t3lib_div::getIndpEnv('HTTP_HOST') . '</td>
									</tr>
									<tr>
										<td><strong>Error:</strong></td>
										<td>' . $errorMessages . '</td>
									</tr>
									<tr>
										<td><strong>IP:</strong></td>
										<td>' . t3lib_div::getIndpEnv('REMOTE_ADDR') . '</td>
									</tr>
									<tr>
										<td><strong>Useragent:</strong></td>
										<td>' . t3lib_div::getIndpEnv('HTTP_USER_AGENT') . '</td>
									</tr>
									<tr>
										<td valign=top><strong>Form values:</strong></td>
										<td>' . $formValues . '</td>
									</tr>
								</table>
							</body>
						</html>
					';

						// Send mail
					$this->htmlMail = t3lib_div::makeInstance('t3lib_htmlmail');
					$this->htmlMail->start();
					$this->htmlMail->recipient = $conf['email_notify'];
					$this->htmlMail->subject = 'Spam recognized in ' . $ext . ' on ' . t3lib_div::getIndpEnv('HTTP_HOST');
					$this->htmlMail->from_email = $conf['email_notify'];
					$this->htmlMail->from_name = 'Spamshield';
					$this->htmlMail->returnPath = $conf['email_notify'];
					$this->htmlMail->setHTML($mailtext);
					if ($this->sendEmail) {
						$this->htmlMail->send($conf['email_notify']);
					}
				} else {
					$info = array(
						'Extension' => $ext,
						'PID' => $GLOBALS['TSFE']->id,
						'URL' => t3lib_div::getIndpEnv('HTTP_HOST'),
						'Error' => $errorMessages,
						'IP' => t3lib_div::getIndpEnv('REMOTE_ADDR'),
						'Useragent' => t3lib_div::getIndpEnv('HTTP_USER_AGENT'),
					);
					foreach ($info as $key => $value) {
						$mailtext .= $key . ': ' . $value . chr(10);
					}
					$mailtext .= chr(10) . 'Form values:' . chr(10);
					foreach ($formArray as $key => $value) {
						$mailtext .= ' * ' . $key . ': ' . $value . chr(10);
					}

					$to = $conf['email_notify'];
					$from = '"Spamshield" <' . $conf['email_notify'] . '>';
					$subject = 'Spam recognized in ' . $ext . ' on ' . t3lib_div::getIndpEnv('HTTP_HOST');
					$headers = 'From: ' . $from;
					mail($to, $subject, $mailtext, $headers);
				}
			}
		}
	}
}

if (defined('TYPO3_MODE')
	&& isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/System/class.tx_wtspamshield_mail.php'])
) {
	require_once ($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/wt_spamshield/Classes/System/class.tx_wtspamshield_mail.php']);
}

?>