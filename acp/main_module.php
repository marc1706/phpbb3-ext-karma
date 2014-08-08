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
	protected $db, $user, $auth, $template, $request;
	protected $config, $phpbb_root_path, $phpEx, $phpbb_container, $root_path, $relative_admin_path;
	public $module_column = array();

	public function __construct()
	{
		global $db, $user, $auth, $template, $phpbb_container, $request;
		global $config, $phpbb_root_path, $phpEx;

		$user->add_lang_ext('phpbb/karma', 'karma');

		$this->root_path = $phpbb_root_path . 'ext/phpbb/karma/';
		$this->relative_admin_path = 'adm/';
		$this->db = $db;
		$this->user = $user;
		$this->template = $template;
		$this->config = $config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->phpbb_container = $phpbb_container;
		$this->auth = $auth;
		$this->request = $request;
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
			case 'config':
				$l_title = 'ACP_KARMA_CONFIG';
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
				$karma = new karma_helper($this->config, $this->user, $this->auth, $this->db, $this->phpbb_container->getParameter('tables.karma.karma'), $this->phpbb_root_path, $this->relative_admin_path, $this->phpEx);

				// Set up general vars
				$start		= request_var('start', 0);
				$delete_mark = ($action == 'del_marked') ? true : false;
				$delete_all	= ($action == 'del_all') ? true : false;
				$marked		= request_var('mark', array(0));

				// Sort keys
				$sort_days	= request_var('st', 0);
				$sort_key	= request_var('sk', 't');
				$sort_dir	= request_var('sd', 'd');

				// Delete entries if requested and able
				if (($delete_mark || $delete_all) && $this->auth->acl_get('a_clearlogs'))
				{
					if (confirm_box(true))
					{
						$where_sql = '';

						if ($delete_mark && sizeof($marked))
						{
							$sql_in = array();
							foreach ($marked as $mark)
							{
								$sql_in[] = $mark;
							}
							$where_sql = ' WHERE ' . $this->db->sql_in_set('karma_id', $sql_in);
							unset($sql_in);

							$sql = 'SELECT *
								FROM ' . $this->phpbb_container->getParameter('tables.karma.karma') .
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
						else if ($delete_all)
						{
							$sql = 'UPDATE ' . USERS_TABLE . '
								SET user_karma_score = 0 ';
							$this->db->sql_query($sql);
						}

						$sql = 'DELETE FROM ' . $this->phpbb_container->getParameter('tables.karma.karma') .
							$where_sql;
						$this->db->sql_query($sql);

						//Add log to ACP index
						add_log('admin', 'LOG_KARMA_CLEAR');
					}
					else
					{
						confirm_box(false, $this->user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
							'start'			=> $start,
							'del_marked'	=> $delete_mark,
							'del_all'		=> $delete_all,
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
					'S_LIMIT_DAYS'		=> $s_limit_days,
					'S_SORT_KEY'		=> $s_sort_key,
					'S_SORT_DIR'		=> $s_sort_dir,
					'S_CLEARHISTORY'	=> $this->auth->acl_get('a_clearlogs'),
					'S_KARMA_HISTORY'	=> true
				));
			break;

			case 'config':
				$display_vars = array(
					'legend'				=> 'ACP_KARMA_CONFIG',
					'karma_minimum'			=> array('lang' => 'ACP_KARMA_MINIMUM',			'validate' => 'int',	'type' => 'text:3:4',		'explain' => true),
					'post_minimum'			=> array('lang' => 'ACP_POST_MINIMUM',			'validate' => 'int',	'type' => 'text:3:4',		'explain' => true),
				);
				$this->new_config = $this->config;
				$cfg_array = ($this->request->is_set('config')) ? $this->request->variable('config', array('' => ''), true) : $this->new_config;
				$error = array();

				// We validate the complete config if whished
				validate_config_vars($display_vars, $cfg_array, $error);

				// Do not write values if there is an error
				if (sizeof($error))
				{
					$submit = false;
				}

				// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
				foreach ($display_vars as $config_name => $null)
				{
					if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
					{
						continue;
					}

					$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

					$submit = ($this->request->is_set_post('submit')) ? true : false;
					if ($submit)
					{
						set_config($config_name, $config_value);
					}
				}

				if ($submit)
				{
					add_log('admin', 'KARMA_LOG_CONFIG');

					trigger_error($this->user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
				}

				$this->template->assign_vars(array(
					'S_ERROR'			=> (sizeof($error)) ? true : false,
					'ERROR_MSG'			=> implode('<br />', $error),
					'S_KARMA_CONFIG'	=> true
				));

				// Output relevant page
				foreach ($display_vars as $config_key => $vars)
				{
					if (!is_array($vars) && strpos($config_key, 'legend') === false)
					{
						continue;
					}

					if (strpos($config_key, 'legend') !== false)
					{
						$this->template->assign_block_vars('options', array(
							'S_LEGEND'		=> true,
							'LEGEND'		=> (isset($this->user->lang[$vars])) ? $this->user->lang[$vars] : $vars
						));

						continue;
					}

					$type = explode(':', $vars['type']);

					$l_explain = '';
					if ($vars['explain'])
					{
						$l_explain = (isset($this->user->lang[$vars['lang'] . '_EXPLAIN'])) ? $this->user->lang[$vars['lang'] . '_EXPLAIN'] : '';
					}

					$this->template->assign_block_vars('options', array(
						'KEY'			=> $config_key,
						'TITLE'			=> (isset($this->user->lang[$vars['lang']])) ? $this->user->lang[$vars['lang']] : $vars['lang'],
						'S_EXPLAIN'		=> $vars['explain'],
						'TITLE_EXPLAIN'	=> $l_explain,
						'CONTENT'		=> build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars),
					));

					unset($display_vars[$config_key]);
				}
			break;

		}
	}
}
