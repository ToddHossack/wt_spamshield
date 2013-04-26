<?php
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('wt_spamshield').'functions/akismet.class.php');

class tx_wtspamshield_method_akismet extends tslib_pibase {

	var $extKey = 'wt_spamshield'; // Extension key of current extension
	
	// Function nameCheck() to disable the same first- and lastname
	function checkAkismet($form, $note) {
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]); // Get backend configuration of this extension
		$error = '';
		
		if(isset($conf)) { // Only if Backendconfiguration exists in localconf
			if($conf['AkismetKey']) { // Only if enabled in backendconfiguration and key was set
				$akismet_array = array(
					//'author'    => $form['firstname'].' '.$form['surname'],
					'author'    => $form['surname'],
					'email'     => $form['email'],
					'website'   => $form['homepage'],
					'body'      => $form['entry'],
					'permalink' => $form['homepage'], 
					'user_ip' => t3lib_div::getIndpEnv('REMOTE_ADDR'),
					'user_agent' => t3lib_div::getIndpEnv('HTTP_USER_AGENT')
				);
				$akismet = new Akismet('http://'.t3lib_div::getIndpEnv('HTTP_HOST').'/', $conf['AkismetKey'], $akismet_array); // new instance for akismet class
				
				if(!$akismet->isError() && $akismet->isSpam()) { // if akismet gives an error
					$error = 'Akismet detected your entry as spam entry<br />'; // default value
					if ($note) $error = $note.'<br />'; // value from tsconfig
				}
			}
		}
		
 		if(isset($error)) return $error; // return error
 
	}

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/lib/class.tx_wtspamshield_method_akismet.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wt_spamshield/lib/class.tx_wtspamshield_method_akismet.php']);
}
?>