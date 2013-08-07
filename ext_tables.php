<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$T3Version = class_exists('t3lib_utility_VersionNumber')
	? t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version)
	: t3lib_div::int_from_ver(TYPO3_version);

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Main Settings');
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Extensions/direct_mail_subscription', 'direct_mail_subscription');
// only add static template for default mailform if T3 < 4.6
if ($T3Version < 4006000) {
	t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Extensions/defaultmailform', 'Default Mailform');
}

$TCA['tx_wtspamshield_log'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:wt_spamshield/Resources/Private/Language/locallang_db.xml:tx_wtspamshield_log',
		'label'     => 'errormsg',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY crdate DESC',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Log.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_wtspamshield_log.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'form, errormsg, formvalues, pageid, ip, useragent',
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_wtspamshield_blacklist');
$TCA['tx_wtspamshield_blacklist'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:wt_spamshield/Resources/Private/Language/locallang_db.xml:tx_wtspamshield_blacklist',
		'label'     => 'value',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY value ASC',
		'delete' => 'deleted',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Blacklist.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_wtspamshield_blacklist.gif',
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'type, value',
	)
);

?>
