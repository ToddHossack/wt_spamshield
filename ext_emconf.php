<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "wt_spamshield".
 *
 * Auto generated 26-04-2013 11:12
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Spamshield',
	'description' => 'Spam shield to avoid spam in powermail and ve_guestbook. Session check, Link check, Time check, Akismet check, Name check (see manual for details)',
	'category' => 'services',
	'shy' => 0,
	'version' => '0.2.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Alexander Kellner',
	'author_email' => 'Alexander.Kellner@einpraegsam.net',
	'author_company' => '',
	'CGLcompliance' => NULL,
	'CGLcompliance_note' => NULL,
	'constraints' => 
	array (
		'depends' => 
		array (
			'php' => '4.0.0-0.0.0',
			'typo3' => '3.5.0-0.0.0',
		),
		'conflicts' => 
		array (
			'mf_akismet' => '0.0.0-9.9.9',
		),
		'suggests' => 
		array (
		),
	),
);

?>