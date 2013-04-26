<?php
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_wtspamshield_mail extends tslib_pibase {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	var $sendEmail = 1; // Disable email sending for testing
	
	// start function for any extension
	function sendEmail($ext,$error,$formArray) {
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension
		
		if(isset($conf)) { // Only if Backendconfiguration exists in localconf
			if($conf['email_notify']) { // Only if email address is enabled
				
				// Prepare mail
				$mailtext = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
					<html>
						<head>
						</head>
						<body>
							<table>
								<tr>
									<td><strong>Extension:</strong></td>
									<td>'.$ext.'</td>
								</tr>
								<tr>
									<td><strong>PID:</strong></td>
									<td>'.$GLOBALS['TSFE']->id.'</td>
								</tr>
								<tr>
									<td><strong>URL:</strong></td>
									<td>'.t3lib_div::getIndpEnv('HTTP_HOST').'</td>
								</tr>
								<tr>
									<td><strong>Error:</strong></td>
									<td>'.$error.'</td>
								</tr>
								<tr>
									<td><strong>IP:</strong></td>
									<td>'.t3lib_div::getIndpEnv('REMOTE_ADDR').'</td>
								</tr>
								<tr>
									<td><strong>Useragent:</strong></td>
									<td>'.t3lib_div::getIndpEnv('HTTP_USER_AGENT').'</td>
								</tr>
								<tr>
									<td valign=top><strong>Form values:</strong></td>
									<td>'.t3lib_div::view_array($formArray).'</td>
								</tr>
							</table>
						</body>
					</html>
				';
				
				// Send mail
				$this->htmlMail = t3lib_div::makeInstance('t3lib_htmlmail');
				$this->htmlMail->start();
				$this->htmlMail->recipient = $conf['email_notify'];
				$this->htmlMail->subject = 'Spam recognized in '.$ext.' on '.t3lib_div::getIndpEnv('HTTP_HOST');
				$this->htmlMail->from_email = $conf['email_notify'];
				$this->htmlMail->from_name = 'Spamshield';
				$this->htmlMail->returnPath = $conf['email_notify'];
				$this->htmlMail->setHTML($mailtext);
				if($this->sendEmail) $this->htmlMail->send($conf['email_notify']);
				
			}
		}
			
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/functions/class.tx_wtspamshield_mail.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/functions/class.tx_wtspamshield_mail.php']);
}
?>