<?php
/**
*
* @package phpBB Karma
* @copyright (c) 2014 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_KARMA'							=> 'Karma',
	'ACP_KARMA_HISTORY'					=> 'Karma History',
	'ACP_KARMA_HISTORY_EXPLAIN'			=> 'This is a list of all karma changes on this board.',
	'ACP_KARMA_CONFIG'					=> 'General Settings',
	'ACP_KARMA_CONFIG_EXPLAIN'			=> 'This is a list of general karma settings.',
	'ACP_KARMA_MINIMUM'					=> 'Needed karma',
	'ACP_KARMA_MINIMUM_EXPLAIN'			=> 'After a user reaches this karma count, the user can give karma',
	'ACP_POST_MINIMUM'					=> 'Needed posts',
	'ACP_POST_MINIMUM_EXPLAIN'			=> 'After a user reaches this post count, the user can give karma',
	'ACP_KARMA_PER_DAY'					=> 'Maximum amount of karmas per day',
	'ACP_KARMA_PER_DAY_EXPLAIN'			=> 'Number of karma changes per day for a single user, zero to disable.',

));
