<?php
/**
*
* @package phpBB Karma
* @copyright (c) 2014 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\karma\acp;

class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\phpbb\karma\acp\main_module.php',
			'title'		=> 'ACP_KARMA',
			'version'	=> '0.0.1',
			'modes'		=> array(
				'history'	=> array('title' => 'ACP_KARMA_HISTORY','auth' => 'acl_a_viewlogs', 'cat' => array('ACP_KARMA')),
				'config'	=> array('title' => 'ACP_KARMA_CONFIG','auth' => 'acl_a_board', 'cat' => array('ACP_KARMA')),
			),
		);
	}
}
