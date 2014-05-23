<?php
/**
*
* @package phpBB Karma
* @copyright (c) 2013 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\karma\ucp;

class received_karma
{
	public function __construct()
	{
		global $config, $phpbb_container, $request, $user, $template;

		$this->config = $config;
		$this->container = $phpbb_container;
		$this->request = $request;
		$this->user = $user;
		$this->template = $template;

		$user->add_lang_ext('phpbb/karma', 'karma');
	}

	public function main($id, $mode)
	{
		global $phpbb_container;
		$this->tpl_name = 'ucp_karma_received_karma';
		$this->page_title = 'UCP_RECEIVED_KARMA';

		$start = $this->request->variable('start', 0);

		// Get the received karma
		$karma_manager = $this->container->get('karma.includes.manager');
		$received_karma_list = $karma_manager->get_karma_received_by_user($this->user->data['user_id'], $this->config['topics_per_page'], $start);
		$received_karma = $received_karma_list['received_karma'];
		$total = $received_karma_list['total'];

		// Put the received karma in a block template variable
		foreach ($received_karma as $row)
		{
			$block_row = array();
			foreach ($row as $key => $value)
			{
				$block_row[strtoupper($key)] = $value;
			}
			$this->template->assign_block_vars('received_karma', $block_row);
		}

		// Generate pagination
		$base_url = $this->u_action;
		$pagination = $phpbb_container->get('pagination');
		$start = $pagination->validate_start($start, $this->config['topics_per_page'], $total);

		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $this->config['topics_per_page'], $start);

		$this->template->assign_vars(array(
			'L_TITLE'			=> $this->user->lang['UCP_RECEIVED_KARMA'],
			'S_REPORT_KARMA'	=> true, // TODO make this depend on permissions and perhaps a setting

			'PAGE_NUMBER'			=> $pagination->on_page($base_url, $total, $this->config['topics_per_page'], $start),
			'TOTAL'					=> $total,
			'TOTAL_RECEIVED_KARMA'	=> $this->user->lang('LIST_RECEIVED_KARMA', (int) $total),
		));
	}
}
