<?php

/**
*
* phpBB Extension - toxyy User Delete Topics
* @copyright (c) 2019 toxyy <thrashtek@yahoo.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'UDT_PERMISSIONS'   => 'Can delete own topics',
]);
