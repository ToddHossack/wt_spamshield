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
				
				// Create formvalues as string
				$formvalue = '';
				if(isset($formArray)) {
					foreach ($formArray as $key => $value) {
						$formvalue .= '<b>'.$key.':</b> '.$value."<br />\n";
					}
				}
				
				// prepare mail and send it
				$mailtext = '<b>Extension:</b> '.$ext.'<br />
					<b>PID:</b> '.$GLOBALS['TSFE']->id.'<br /> 
					<b>URL:</b> '.$_SERVER['HTTP_HOST'].'<br />
					<b>Error:</b> '.$error.'<br />
					<b>IP:</b> '.$_SERVER['REMOTE_ADDR'].'<br />
					<b>Useragent:</b> '.$_SERVER['HTTP_USER_AGENT'].'<br />';
				$mailtext .= $formvalue;
				$this->htmlMail = t3lib_div::makeInstance('t3lib_htmlmail');
				$this->htmlMail->start();
				$this->htmlMail->recipient = $conf['email_notify'];
				$this->htmlMail->subject = 'Spam recognized in '.$ext.' on '.$_SERVER['HTTP_HOST'];
				$this->htmlMail->from_email = $conf['email_notify'];
				$this->htmlMail->from_name = $conf['email_notify'];
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