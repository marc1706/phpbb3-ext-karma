<?php
/**
*
* @package phpBB Karma
* @copyright (c) 2014 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\karma\acp;

// Table name
global $table_prefix;
define('KARMA_TABLE', $table_prefix . 'karma');

class karma_helper
{
	/**
	* Config object
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Auth object
	* @var \phpbb\auth\auth
	*/
	protected $auth;

	/**
	* Karma table
	* @var string
	*/
	protected $karma_table;

	/**
	* Constructor
	* @param \phpbb\config\config		$config					Config object
	* @param \phpbb\user				$user					User object
	* @param \phpbb\auth\auth			$auth					Auth object
	* @param string				$karma_table	Karma table
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \phpbb\auth\auth $auth, $karma_table)
	{
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->karma_table = $karma_table;
	}

	/**
	* Displays history of all karma given
	* @param	array		$history				Array of history entries
	* @param	int			$history_count			Total count of history enteries returned
	* @param	int			$limit					Limit the number of entries that are returned
	* @param	int			$offset					Offset when fetching the history entries, when pagination
	* @param	int			$limit_days				Limit the number of days for which entries are returned
	* @param	strint		$sort_by				Order in which to sort the history entries
	*/
	function view_history(&$history, &$history_count, $limit = 0, $offset = 0, $limit_days = 0, $sort_by = 'k.karma_time DESC')
	{
		global $config, $db, $user, $auth, $phpEx, $phpbb_root_path, $phpbb_admin_path;

		$giving_user_id_list = $is_auth = $is_mod = array();

		$profile_url = (defined('IN_ADMIN')) ? append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=overview') : append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=viewprofile');

		$sql = "SELECT k.*, u.username, u.username_clean, u.user_colour, u.user_ip
			FROM ". $this->karma_table ." k, " . USERS_TABLE . " u
			WHERE u.user_id = k.receiving_user_id
				" . (($limit_days) ? "AND k.karma_time >= $limit_days" : '') . "
			ORDER BY $sort_by";
		$result = $db->sql_query_limit($sql, $limit, $offset);

		$i = 0;
		$history = array();
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['giving_user_id'])
			{
				$giving_user_id_list[] = $row['giving_user_id'];
			}

			$history[$i] = array(
				'id'					=> $row['karma_id'],

				'giving_user_id'		=> $row['giving_user_id'],
				'giving_username'		=> '',
				'giving_username_full'	=> '',

				'user_id'				=> $row['receiving_user_id'],
				'username'				=> $row['username'],
				'username_full'			=> get_username_string('full', $row['receiving_user_id'], $row['username'], $row['user_colour'], false, $profile_url),

				'ip'					=> $row['user_ip'],
				'time'					=> $row['karma_time'],
				'comment'				=> $row['karma_comment'],
				'action'				=> $row['karma_score'],
			);
			$i++;
		}

		$db->sql_freeresult($result);

		if (sizeof($giving_user_id_list))
		{
			$giving_user_id_list = array_unique($giving_user_id_list);
			$giving_user_names_list = array();

			$sql = 'SELECT user_id, username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $giving_user_id_list);
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$giving_user_names_list[$row['user_id']] = $row;
			}
			$db->sql_freeresult($result);

			foreach ($history as $key => $row)
			{
				if (!isset($giving_user_names_list[$row['giving_user_id']]))
				{
					continue;
				}

				$history[$key]['giving_username'] = $giving_user_names_list[$row['giving_user_id']]['username'];
				$history[$key]['giving_username_full'] = get_username_string('full', $row['giving_user_id'], $giving_user_names_list[$row['giving_user_id']]['username'], $giving_user_names_list[$row['giving_user_id']]['user_colour'], false, $profile_url);
			}
		}

		$sql = 'SELECT COUNT(k.karma_id) AS total_entries
			FROM ' . $this->karma_table . " k
			WHERE k.karma_time >= $limit_days";
		$result = $db->sql_query($sql);
		$history_count = (int) $db->sql_fetchfield('total_entries');
		$db->sql_freeresult($result);

		return;
	}
}
