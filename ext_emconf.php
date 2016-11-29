<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "wt_spamshield".
 *
 * Auto generated 13-11-2013 00:28
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Spamshield',
	'description' => 'Spam shield without captcha to avoid spam in powermail, ve_guestbook, comments, t3_blog, direct_mail_subscription and standard TYPO3 mailforms. Session check, Link check, Time check, Akismet check, Name check, Honeypot check (see manual for details)',
	'category' => 'services',
	'shy' => 0,
	'version' => '1.3.1',
	'dependencies' => '',
	'conflicts' => 'mf_akismet,wt_calculating_captcha',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Bjoern Jacob, Ralf Zimmermann, Alex Kellner',
	'author_email' => 'bj@tritum.de, rz@tritum.de, alexander.kellner@in2code.de',
	'author_company' => 'TRITUM, in2code',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-0.0.0',
			'typo3' => '4.5.0-6.2.99',
		),
		'conflicts' => array(
			'mf_akismet' => '0.0.0-9.9.9',
			'wt_calculating_captcha' => '0.0.0-0.0.0',
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => '',
);

?>
