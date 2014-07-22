<?php
/**
*
* @package phpBB Karma Testing
* @copyright (c) 2014 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\karma\tests\functional;

/**
* @group functional
*/
class karma_acp_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('phpbb/karma');
	}

	public function setUp()
	{
		parent::setUp();

		$this->login();
		$this->admin_login();
		$this->add_lang_ext('phpbb/karma', 'info_acp_karma');
		$this->add_lang_ext('phpbb/karma', 'karma');
	}

	public function test_karma_history()
	{
		$crawler = $this->request('GET', 'adm/index.php?i=\phpbb\karma\acp\main_module&mode=history&sid=' . $this->sid);
		$this->assertContainsLang('ACP_KARMA_HISTORY', $crawler->text());
	}

	public function test_delete_all_karma()
	{
		$this->create_and_karma_post('test_user1');
		$crawler = $this->request('GET', 'adm/index.php?i=\phpbb\karma\acp\main_module&mode=history&sid=' . $this->sid);
		$this->assertContains('test_user1', $crawler->filter('html')->text());
		$form = $crawler->selectButton('action[del_all]')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIRM_OPERATION', $crawler->text());
		$form = $crawler->selectButton('confirm')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('NO_ENTRIES', $crawler->text());
		$this->delete_test_user_post('test_user1');
	}

	public function test_delete_marked_karma()
	{
		$this->create_and_karma_post('test_user2');
		$crawler = $this->request('GET', 'adm/index.php?i=\phpbb\karma\acp\main_module&mode=history&sid=' . $this->sid);
		$this->assertContains('test_user2', $crawler->filter('html')->text());
		$form = $crawler->selectButton('action[del_marked]')->form();
		$form['mark[0]']->tick();
		$crawler = self::submit($form);
		$this->assertContains($this->lang('CONFIRM_OPERATION'), $crawler->text());
		$form = $crawler->selectButton('confirm')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('NO_ENTRIES', $crawler->text());
		$this->delete_test_user_post('test_user2');
	}

	protected function create_and_karma_post($user_name)
	{
		$this->logout();
		$uid = $this->create_user($user_name);
		if (!$uid)
		{
			$this->markTestIncomplete('Unable to create test_user');
		}
		$this->login($user_name);

		$post = $this->create_post(2, 1, 'Testing Subject', 'This is a test post by test_user.', array());

		$this->logout();
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('GIVEKARMA_POSITIVE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/')) ."&sid={$this->sid}");
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());

		$form = $crawler->selectButton('submit')->form();
		$form['karma_score']->select('1');
		$form['karma_comment'] = 'Positive Karma Comment';
		$crawler = self::submit($form);
	}

	protected function delete_test_user_post($test_user)
	{
		$crawler = self::request('GET', "memberlist.php");
		$profile_link = $crawler->selectLink($test_user)->link()->getUri();
		$crawler = self::request('GET', substr($profile_link, strpos($profile_link, 'memberlist.php?mode=viewprofile')));
		$this->assertContains('Karma: 0', $crawler->filter('html')->text());

		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('DELETE_POST', '', ''))->eq(1)->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'posting.php?mode=delete')) ."&sid={$this->sid}");
		$form = $crawler->selectButton('Yes')->form();
		$form['delete_permanent']->tick();
		$crawler = self::submit($form);

		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$this->assertNotContains('Testing Subject', $crawler->filter('html')->text());
	}
}
