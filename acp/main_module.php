<?php
/**
*
* @package phpBB Karma
* @copyright (c) 2014 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\karma\acp;

/**
* @package acp
*/
class main_module
{
	public $u_action;
	public $new_config = array();
	protected $db, $user, $auth, $template;
	protected $config, $phpbb_root_path, $phpEx, $phpbb_container, $root_path;
	public $module_column = array();

	public function __construct()
	{
		global $db, $user, $auth, $template, $phpbb_container;
		global $config, $phpbb_root_path, $phpEx;

		$user->add_lang_ext('phpbb/karma', 'karma');

		$this->root_path = $phpbb_root_path . 'ext/phpbb/karma/';
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->phpbb_container = $phpbb_container;
		$this->auth = $auth;
	}

	function main($id, $mode)
	{
		$submit = (isset($_POST['submit'])) ? true : false;

		$action = request_var('action', array('' => ''));

		if (is_array($action))
		{
			list($action, ) = each($action);
		}
		else
		{
			$action = request_var('action', '');
		}

		switch ($mode)
		{
			case 'history':
				$l_title = 'ACP_KARMA_HISTORY';
			break;
		}

		$this->tpl_name = 'acp_karma';
		$this->page_title = $l_title;

		$this->template->assign_vars(array(
			'L_TITLE'			=> $this->user->lang[$l_title],
			'L_TITLE_EXPLAIN'	=> $this->user->lang[$l_title . '_EXPLAIN'],
			'U_ACTION'			=> $this->u_action)
		);

		switch ($mode)
		{
			case 'history':
				include($this->root_path . 'includes/functions_karma.' . $this->phpEx);
				$karma = new \karmaext();

				// Set up general vars
				$start		= request_var('start', 0);
				$deletemark = ($action == 'del_marked') ? true : false;
				$deleteall	= ($action == 'del_all') ? true : false;
				$marked		= request_var('mark', array(0));

				// Sort keys
				$sort_days	= request_var('st', 0);
				$sort_key	= request_var('sk', 't');
				$sort_dir	= request_var('sd', 'd');

				// Delete entries if requested and able
				if (($deletemark || $deleteall) && $this->auth->acl_get('a_clearlogs'))
				{
					if (confirm_box(true))
					{
						$where_sql = '';

						if ($deletemark && sizeof($marked))
						{
							$sql_in = array();
							foreach ($marked as $mark)
							{
								$sql_in[] = $mark;
							}
							$where_sql = ' WHERE ' . $this->db->sql_in_set('karma_id', $sql_in);
							unset($sql_in);

							$sql = 'SELECT *
								FROM ' . KARMA_TABLE .
								$where_sql;
							$result = $this->db->sql_query($sql);

							while ($row = $this->db->sql_fetchrow($result))
							{
								$sql = 'UPDATE ' . USERS_TABLE . '
									SET user_karma_score = user_karma_score - '. ($row['karma_score']) . '
									WHERE user_id = ' . $row['receiving_user_id'];
								$this->db->sql_query($sql);
							}
						}

						else if ($deleteall)
						{
							$sql = 'UPDATE ' . USERS_TABLE . '
								SET user_karma_score = 0 ';
							$this->db->sql_query($sql);
						}

						$sql = 'DELETE FROM ' . KARMA_TABLE .
							$where_sql;
						$this->db->sql_query($sql);

						//Add log to ACP index
						add_log('admin', 'LOG_KARMA_CLEAR');
					}
					else
					{
						confirm_box(false, $this->user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
							'start'			=> $start,
							'del_marked'	=> $deletemark,
							'del_all'		=> $deleteall,
							'mark'			=> $marked,
							'st'			=> $sort_days,
							'sk'			=> $sort_key,
							'sd'			=> $sort_dir,
							'i'				=> $id,
							'mode'			=> $mode,
							'action[]'		=> $action))
						);
					}
				}

				// Sorting
				$limit_days = array(0 => $this->user->lang['ALL_ENTRIES'], 1 => $this->user->lang['1_DAY'], 7 => $this->user->lang['7_DAYS'], 14 => $this->user->lang['2_WEEKS'], 30 => $this->user->lang['1_MONTH'], 90 => $this->user->lang['3_MONTHS'], 180 => $this->user->lang['6_MONTHS'], 365 => $this->user->lang['1_YEAR']);
				$sort_by_text = array('u' => $this->user->lang['SORT_USERNAME'], 't' => $this->user->lang['SORT_DATE'], 'i' => $this->user->lang['SORT_IP'], 'a' => $this->user->lang['SORT_ACTION']);
				$sort_by_sql = array('u' => 'u.username_clean', 't' => 'k.karma_time', 'i' => 'u.user_ip', 'a' => 'k.karma_score');

				$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
				gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

				// Define where and sort sql for use in displaying logs
				$sql_where = ($sort_days) ? (time() - ($sort_days * 86400)) : 0;
				$sql_sort = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

				// Grab history data
				$history_data = array();
				$history_count = 0;
				$karma->view_history($history_data, $history_count, $this->config['topics_per_page'], $start, $sql_where, $sql_sort);

				foreach ($history_data as $row)
				{
					$data = array();

					$this->template->assign_block_vars('history', array(
						'USERNAME'				=> $row['username_full'],
						'GIVING_USER_USERNAME'	=> $row['giving_username_full'],
						'IP'					=> $row['ip'],
						'DATE'					=> $this->user->format_date($row['time']),
						'ACTION'				=> $row['action'],
						'COMMENT'				=> (!empty($row['comment'])) ? $row['comment'] : '',
						'ID'					=> $row['id'],
					));
				}

				//Pagination
				$pagination = $this->phpbb_container->get('pagination');
				$start = $pagination->validate_start($start, $this->config['topics_per_page'], $history_count);
				$pagination->generate_template_pagination($this->u_action, 'pagination', 'start', $history_count, $this->config['topics_per_page'], $start);

				$this->template->assign_vars(array(
					'U_ACTION'			=> $this->u_action,
					'S_ON_PAGE'			=> $pagination->on_page($this->u_action, $history_count, $this->config['topics_per_page'], $start),
					'S_LIMIT_DAYS'		=> $s_limit_days,
					'S_SORT_KEY'		=> $s_sort_key,
					'S_SORT_DIR'		=> $s_sort_dir,
					'S_CLEARHISTORY'	=> $this->auth->acl_get('a_clearlogs'),
					'S_KARMA_HISTORY'	=> true
				));
			break;

		}
	}
}

?>
