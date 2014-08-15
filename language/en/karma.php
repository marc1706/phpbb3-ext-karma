<?php
/**
*
* @package phpBB Karma
* @copyright (c) 2013 phpBB
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

// These translations are only needed where the extension can load them in time
$lang = array_merge($lang, array(
	'KARMA'								=> 'Karma',
	'KARMA_GIVE_KARMA'					=> 'Give karma',
	'KARMA_EDIT_KARMA'					=> 'Edit karma',
	'KARMA_DELETE_KARMA'				=> 'Delete karma',
	'KARMA_GIVING_KARMA'				=> 'You’re giving karma on %2$s by %3$s.',
	'KARMA_EDITING_KARMA'				=> 'You’re editing the karma %1$s gave on %2$s by %3$s.',
	'KARMA_EDITING_KARMA_YOU'			=> 'you',
	'KARMA_GIVE_KARMA_DESCR'			=> 'By giving karma, you can praise users for contributing something good or criticize them for bad behaviour. Given karma will increase/decrease the receiving user’s karma score. This score is publicly visible and indicates how the community has valued the contributions of a user thus far.',
	'KARMA_SCORE'						=> 'Score',
	'KARMA_SCORE_DESCR'					=> 'Is this item a good contribution?',
	'KARMA_SCORE_POSITIVE'				=> 'Positive',
	'KARMA_SCORE_NEGATIVE'				=> 'Negative',
	'KARMA_COMMENT'						=> 'Comment',
	'KARMA_COMMENT_DESCR'				=> 'Why do you think this item is good/bad?',
	'KARMA_SCORE_OUTOFBOUNDS'			=> 'Invalid karma score.',
	'KARMA_TIME_TOO_LARGE'				=> 'Karma time too large.',
	'KARMA_SCORE_INVALID'				=> 'Please select a karma score.',
	'KARMA_SUCCESSFULLY_GIVEN'			=> 'Karma successfully given.',
	'KARMA_SUCCESSFULLY_EDITED'			=> 'Karma successfully edited.',
	'KARMA_SUCCESSFULLY_DELETED'		=> 'Karma successfully deleted.',
	'KARMA_VIEW_ITEM'					=> '%sView the item you gave karma on%s',
	'NO_KARMA_TYPE'						=> 'Karma type %s does not exist.',
	'GIVEKARMA_POSITIVE'				=> 'Give positive karma',
	'GIVEKARMA_NEGATIVE'				=> 'Give negative karma',
	'KARMA_RECEIVED_KARMA'				=> 'Below you can see all karma that has been given on contributions by you.',
	'KARMA_RECEIVED_ON_ITEM'			=> 'Received on',
	'KARMA_RECEIVED_TIME'				=> 'Received at',
	'KARMA_GIVEN_BY'					=> 'Given by',
	'KARMA_NO_RECEIVED_KARMA'			=> 'No karma received yet.',
	'NO_KARMA'							=> 'The requested karma does not exist.',
	'KARMA_REPORT_TIME_TOO_LARGE'		=> 'Karma report time too large.',
	'KARMA_REPORT'						=> 'Report',
	'KARMA_SUCCESSFULLY_REPORTED'		=> 'Karma successfully reported.',
	'KARMA_VIEW_REPORTED_KARMA'			=> '%sView the karma you reported%s',
	'KARMA_REPORT_KARMA'				=> 'Report karma',
	'KARMA_REPORT_KARMA_DESCR'			=> 'Use this form to report received karma to the forum moderators and board administrators. Reporting should generally be used only if the karma is unfair or abusive.',
	'KARMA_REPORTING_KARMA'				=> 'You’re reporting the following karma:',
	'KARMA_REPORT_DESCRIPTION'			=> 'Why are you reporting this karma?',
	'KARMA_REPORT_TEXT_EMPTY'			=> 'You must specify why you’re reporting this karma.',
	'KARMA_ALREADY_REPORTED'			=> 'This karma has already been reported.',
	'MCP_REPORTED_KARMA'				=> 'Reported karma',
	'NO_KARMA_REPORT'					=> 'The requested karma report does not exist.',
	'KARMA_REPORT_REASON'				=> 'Report reason',
	'KARMA_REPORT_DETAILS'				=> 'Report details.',
	'KARMA_REPORTED_BY'					=> 'Reported by',
	'KARMA_REPORTED_KARMA'				=> 'The reported karma',
	'KARMA_REPORT_ITEM_EDITED'			=> 'This item was edited after the karma was given',
	'KARMA_REPORT_KARMA_EDITED'			=> 'Note: the reported karma has been altered since it was reported. It now is',
	'KARMA_REPORT_CLOSED_SUCCESS'		=> 'The selected karma report has been closed successfully.',
	'KARMA_REPORTS_CLOSED_SUCCESS'		=> 'The selected karma reports have been closed successfully.',
	'KARMA_REPORT_DELETED_SUCCESS'		=> 'The selected karma report has been deleted successfully.',
	'KARMA_REPORTS_DELETED_SUCCESS'		=> 'The selected karma reports have been deleted successfully.',
	'REPORTED_KARMA_SUMMARY'			=> '%1$s on “%2$s”',
	'MCP_KARMA_REPORTS_OPEN_EXPLAIN'	=> 'This is a list of all reported karma that is still to be handled.',
	'MCP_KARMA_REPORTS_CLOSED_EXPLAIN'	=> 'This is a list of all reported karma that has previously been resolved.',
	'MCP_KARMA_REPORTS_OPEN'			=> 'Karma reports',
	'MCP_KARMA_REPORTS_CLOSED'			=> 'Karma reports',
	'KARMA_DELETED'						=> '[karma deleted]',
	'KARMA_GIVEN_TO'					=> 'To',
	'LIST_KARMA_REPORTS'				=> array(
		1	=> '%d karma report',
		2	=> '%d karma reports',
	),
	'LIST_RECEIVED_KARMA'				=> array(
		1	=> '%d received karma',
		2	=> '%d received karma',
	),
	'SORRY_AUTH_KARMA'					=> 'You are not authorised to give karma.',
	'NO_SELF_KARMA'						=> 'You are not allowed to give karma to yourself.',
	'SORRY_AUTH_KARMA_EDIT'				=> 'You are not authorised to edit this karma.',
	'SORRY_AUTH_KARMA_DELETE'			=> 'You are not authorised to delete this karma.',
	'GIVEN_KARMA_DESC'					=> 'You gave this %1$s with comment “%2$s”. Click to edit the karma you gave.',
	'KARMA_SCORE_NONE'					=> 'None (undo giving this karma)',
	'KARMA_DELETE_CONFIRM'				=> 'Are you sure you want to delete this karma?',
	'KARMA_ALREADY_REPORTED'			=> 'Reported',
	'NO_REPORT_OTHERS_KARMA'			=> 'You are not allowed to report karma given to others.',
	'INSUFFICIENT_KARMA'				=> 'You are not allowed to give karma as you do not have minimum needed karma.',
	'INSUFFICIENT_POSTS'				=> 'You are not allowed to give karma as you do not have minimum needed posts.',
	'ACP_KARMA_APPEND_TIMES'			=> 'times',
	'ACP_KARMA_APPEND_POSTS'			=> 'posts',
	'KARMA_PER_DAY_LIMIT_REACHED'		=> 'You are not allowed to give karma now as you have reached the karma per day limit.',

));
