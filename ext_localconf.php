<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');



/* Using HOOKS in other extensions */

// Hook for using the plugin with powermail (Formwrap)
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_FormWrapMarkerHook'][] = 'EXT:wt_spamshield/ext/class.tx_wtspamshield_powermail.php:tx_wtspamshield_powermail';

// Hook for using the plugin with powermail (Before sending mails)
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_SubmitBeforeMarkerHook'][] = 'EXT:wt_spamshield/ext/class.tx_wtspamshield_powermail.php:tx_wtspamshield_powermail';

// Hook for using the plugin in ve_guestbook: Set timestamp in session
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ve_guestbook']['extraItemMarkerHook'][] = 'EXT:wt_spamshield/ext/class.tx_wtspamshield_ve_guestbook.php:tx_wtspamshield_ve_guestbook';

// Hook for using the plugin in ve_guestbook: Form check
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ve_guestbook']['formvalidation'][] = 'EXT:wt_spamshield/ext/class.tx_wtspamshield_ve_guestbook.php:tx_wtspamshield_ve_guestbook';
?>