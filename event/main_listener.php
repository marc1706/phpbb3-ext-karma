<?php
/**
*
* @package phpBB Karma
* @copyright (c) 2013 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\karma\event;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
    exit;
}

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			// Core events
			'core.permissions'						=> 'add_permissions',
			'core.user_setup'						=> 'load_global_translations',
			'core.viewtopic_get_post_data'			=> 'viewtopic_body_retrieve_given_karma_with_posts',
			'core.viewtopic_post_rowset_data'		=> 'viewtopic_body_add_given_karma_to_postrow',
			'core.viewtopic_cache_user_data'		=> 'viewtopic_body_add_karma_score_to_user_cache_data',
			'core.viewtopic_modify_post_row'		=> 'viewtopic_body_postrow_add_karma_score_and_controls',
			'core.ucp_pm_view_messsage'				=> 'ucp_pm_viewmessage_add_pm_author_karma_score',
			'core.memberlist_prepare_profile_data'	=> 'memberlist_view_add_karma_score_to_user_statistics',

			// Extension events
			'ext_phpbb_karma.delete_karma_before'	=> 'delete_karma_reports_when_karma_is_deleted',
		);
	}

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Controller helper object
	* @var \phpbb\controller\helper
	*/
	protected $helper;

	/**
	* Karma manager object
	* @var \phpbb\karma\includes\manager
	*/
	protected $karma_manager;

	/**
	* Karma manager object
	* @var \phpbb\karma\includes\report_model
	*/
	protected $karma_report_model;

	/**
	* Name of the karma_reports database table
	* @var string
	*/
	protected $karma_table;

	/**
	* Name of the karma_reports database table
	* @var string
	*/
	protected $karma_types_table;

	public function __construct(\phpbb\user $user, \phpbb\controller\helper $helper, \phpbb\karma\includes\manager $karma_manager, \phpbb\karma\includes\report_model $karma_report_model, $karma_table, $karma_types_table)
	{
		$this->user = $user;
		$this->helper = $helper;
		$this->karma_manager = $karma_manager;
		$this->karma_report_model = $karma_report_model;
		$this->karma_table = $karma_table;
		$this->karma_types_table = $karma_types_table;
	}

	public function add_permissions($event)
	{
		// Add a permission category for karma
		$categories = $event['categories'];
		$categories['karma'] = 'ACL_CAT_KARMA';
		$event['categories'] = $categories;

		// Add permissions for karma
		$permissions = $event['permissions'];
		$permissions['u_givekarma'] = array('lang' => 'ACL_U_GIVEKARMA', 'cat' => 'karma');
		$permissions['u_karma_edit'] = array('lang' => 'ACL_U_KARMA_EDIT', 'cat' => 'karma');
		$permissions['u_karma_delete'] = array('lang' => 'ACL_U_KARMA_DELETE', 'cat' => 'karma');
		$permissions['m_karma_report'] = array('lang' => 'ACL_M_KARMA_REPORT', 'cat' => 'karma');
		$permissions['m_karma_edit'] = array('lang' => 'ACL_M_KARMA_EDIT', 'cat' => 'karma');
		$permissions['m_karma_delete'] = array('lang' => 'ACL_M_KARMA_DELETE', 'cat' => 'karma');
		$event['permissions'] = $permissions;
	}

	public function load_global_translations($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'phpbb/karma',
			'lang_set' => 'karma_global',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function viewtopic_body_retrieve_given_karma_with_posts($event)
	{
		// Extend the post information retrieval query to retrieve any karma given by $this->user on any of the posts
		$sql_ary = $event['sql_ary'];
		$sql_ary['SELECT'] .= ', karma.karma_score, karma.karma_comment';
		if (!isset($sql_ary['LEFT_JOIN']))
		{
			$sql_ary['LEFT_JOIN'] = array();
		}
		$sql_ary['LEFT_JOIN'][] = array(
			'FROM'	=> array(
				$this->karma_table		=> 'karma',
			),
			'ON'	=> "karma.karma_type_id = (
							SELECT karma_type_id
							FROM $this->karma_types_table
							WHERE karma_type_name = 'post'
						)
						AND karma.item_id = p.post_id
						AND karma.giving_user_id =" . (int) $this->user->data['user_id'],
						// TODO that 'post' type probably shouldn't be hardcoded. Perhaps a definition somewhere?
						// TODO this could be done using a cross join if only the LEFT_JOIN sub-array supported that
		);
		$event['sql_ary'] = $sql_ary;
	}

	public function viewtopic_body_add_given_karma_to_postrow($event)
	{
		if (isset($event['row']['karma_score']))
		{
			$rowset_data = $event['rowset_data'];
			$fields_to_copy = array('karma_score', 'karma_comment');
			foreach ($fields_to_copy as $field)
			{
				$rowset_data[$field] = $event['row'][$field];
			}
			$event['rowset_data'] = $rowset_data;
		}
	}

	public function viewtopic_body_add_karma_score_to_user_cache_data($event)
	{
		$user_cache_data = $event['user_cache_data'];
		$user_cache_data['karma_score'] = $event['row']['user_karma_score'];
		$event['user_cache_data'] = $user_cache_data;
	}

	public function viewtopic_body_postrow_add_karma_score_and_controls($event)
	{
		if ($event['row']['user_id'] != ANONYMOUS)
		{
			// Load the karma language file
			$this->user->add_lang_ext('phpbb/karma', 'karma');

			// Add the user's karma score to the template
			$post_row = $event['post_row'];
			$post_row['POSTER_KARMA_SCORE'] = $this->karma_manager->format_karma_score($event['user_poster_data']['karma_score']);

			if ($event['row']['user_id'] != $this->user->data['user_id'])
			{
				// Add the URLs for the karma controls (thumbs up/down)
				$post_row['U_GIVEKARMA_POSITIVE'] = $this->helper->url("givekarma/post/{$event['row']['post_id']}", 'score=positive');
				$post_row['U_GIVEKARMA_NEGATIVE'] = $this->helper->url("givekarma/post/{$event['row']['post_id']}", 'score=negative');

				// Add a description if the user already gave karma on this post
				if (isset($event['row']['karma_score']) && $event['row']['karma_score'] != 0)
				{
					$post_row[
						($event['row']['karma_score'] > 0)
						? 'S_GIVEN_KARMA_POSITIVE'
						: 'S_GIVEN_KARMA_NEGATIVE'
					] = true;
					$post_row['GIVEN_KARMA_DESC'] = sprintf(
						$this->user->lang['GIVEN_KARMA_DESC'],
						$this->karma_manager->format_karma_score($event['row']['karma_score']),
						$event['row']['karma_comment']
					);
				}
			}
			$event['post_row'] = $post_row;
		}
	}

	public function ucp_pm_viewmessage_add_pm_author_karma_score($event)
	{
		// Load the karma language file
		$this->user->add_lang_ext('phpbb/karma', 'karma');

		// Add the karma score to the template variables
		$msg_data = $event['msg_data'];
		$msg_data['AUTHOR_KARMA_SCORE'] = $this->karma_manager->format_karma_score($event['message_row']['user_karma_score']);
		$event['msg_data'] = $msg_data;
	}

	public function memberlist_view_add_karma_score_to_user_statistics($event)
	{
		// Load the karma language file
		$this->user->add_lang_ext('phpbb/karma', 'karma');

		// Add the karma score to the template variables
		$template_data = $event['template_data'];
		$template_data['USER_KARMA_SCORE'] = $this->karma_manager->format_karma_score($event['data']['user_karma_score']);
		$event['template_data'] = $template_data;
	}

	public function delete_karma_reports_when_karma_is_deleted($event)
	{
		$this->karma_report_model->delete_karma_reports_by_karma_ids($event['karma_id_list'], false);
	}
}
