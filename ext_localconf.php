<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$t3Version = class_exists('t3lib_utility_VersionNumber')
	? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version)
	: t3lib_div::int_from_ver(TYPO3_version);

/* Use HOOKS in other extensions */

	// Hook Powermail: Generate Form
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_FormWrapMarkerHook'][]
	= 'EXT:wt_spamshield/Classes/Extensions/class.tx_wtspamshield_powermail.php:tx_wtspamshield_powermail';

	// Hook Powermail: Give error to Powermail
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['powermail']['PM_SubmitBeforeMarkerHook'][]
	= 'EXT:wt_spamshield/Classes/Extensions/class.tx_wtspamshield_powermail.php:tx_wtspamshield_powermail';

	// Hook ve_guestbook: Generate Form
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ve_guestbook']['extraItemMarkerHook'][]
	= 'EXT:wt_spamshield/Classes/Extensions/class.tx_wtspamshield_ve_guestbook.php:tx_wtspamshield_ve_guestbook';

	// Hook ve_guestbook: Give error to guestbook
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ve_guestbook']['preEntryInsertHook'][]
	= 'EXT:wt_spamshield/Classes/Extensions/class.tx_wtspamshield_ve_guestbook.php:tx_wtspamshield_ve_guestbook';

$extPath = t3lib_extMgm::extPath('wt_spamshield');
	// Validator/ Hook standard mailform: Disable email
if ($t3Version >= 6000000 AND t3lib_extMgm::isLoaded('form')) {
	$txFormValidator = $extPath . 'Classes/Extensions/WtspamshieldValidator.php';
	require_once($txFormValidator);
} elseif ($t3Version >= 4006000 AND t3lib_extMgm::isLoaded('form')) {
	$txFormValidator = $extPath . 'Classes/Extensions/class.tx_form_System_Validate_Wtspamshield.php';
	require_once($txFormValidator);
} else {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['sendFormmail-PreProcClass'][]
		= 'EXT:wt_spamshield/Classes/Extensions/class.tx_wtspamshield_defaultmailform.php:tx_wtspamshield_defaultmailform';
}

	// Hook direct_mail_subscription
	// Sorry, there is no better way, the autoloader does not work
if (t3lib_extMgm::isLoaded('direct_mail_subscription')) {
	$directMailSubscription = t3lib_extMgm::extPath('direct_mail_subscription') . 'fe_adminLib.inc';
	require_once($directMailSubscription);
}

	// Hook tx_comments: Generate Form
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['form'][]
	= 'EXT:wt_spamshield/Classes/Extensions/class.tx_wtspamshield_comments.php:tx_wtspamshield_comments->form';

	// Hook tx_comments: Give error to comments
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['comments']['externalSpamCheck'][]
	= 'EXT:wt_spamshield/Classes/Extensions/class.tx_wtspamshield_comments.php:tx_wtspamshield_comments->externalSpamCheck';

	// Hook tx_keuserregister: Generate Form
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_keuserregister']['additionalMarkers'][]
	= 'EXT:wt_spamshield/Classes/Extensions/class.tx_wtspamshield_ke_userregister.php:tx_wtspamshield_ke_userregister';

	// Hook ke_userregister: Give error to ke_userregister
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tx_keuserregister']['specialEvaluations'][]
	= 'EXT:wt_spamshield/Classes/Extensions/class.tx_wtspamshield_ke_userregister.php:tx_wtspamshield_ke_userregister';

	// Hook t3_blog
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['t3blog']['aftercommentinsertion'][]
	= 'EXT:wt_spamshield/Classes/Extensions/class.tx_wtspamshield_t3blog.php:tx_wtspamshield_t3blog->insertNewComment';

?>