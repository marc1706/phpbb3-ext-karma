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
class report_karma_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('phpbb/karma');
	}

	public function setUp()
	{
		parent::setUp();

		$this->add_lang_ext('phpbb/karma', 'karma');
		$this->add_lang_ext('phpbb/karma', 'karma_global');
		$this->add_lang('mcp');
	}

	protected function create_and_karma_post()
	{
		$this->login();
		$this->admin_login();
		$post = $this->create_post(2, 1, 'Testing Subject 1', 'This is a test post 1 by admin as test_user.', array());
		$post = $this->create_post(3, 1, 'Testing Subject 2', 'This is a test post 2 by admin as test_user.', array());

		$this->logout();
		$uid = $this->create_user('test_report_user');
		if (!$uid)
		{
			$this->markTestIncomplete('Unable to create test_user');
		}
		$this->login('test_report_user');

		$this->give_karma(1);
		$this->give_karma(2);
	}

	protected function give_karma($i)
	{
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('GIVEKARMA_POSITIVE', '', ''))->eq($i)->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/')) ."&sid={$this->sid}");
		$this->assertContains('Testing Subject ' . $i, $crawler->filter('html')->text());

		$form = $crawler->selectButton('submit')->form();
		$form['karma_score']->select('1');
		$form['karma_comment'] = 'Positive Karma Comment';
		$crawler = self::submit($form);
	}

	public function test_report_karma()
	{
		$this->create_and_karma_post();
		$this->logout();
		$this->login();
		$this->admin_login();
		$crawler = $this->request('GET', 'ucp.php?i=\phpbb\karma\ucp\received_karma&sid=' . $this->sid);
		$this->assertContainsLang('UCP_RECEIVED_KARMA', $crawler->text());
		$this->assertContains('Testing Subject 1', $crawler->filter('html')->text());
		$this->assertContains('Testing Subject 2', $crawler->filter('html')->text());
		$this->report_karma();
		$this->report_karma();
	}

	protected function report_karma()
	{
		$crawler = $this->request('GET', 'ucp.php?i=\phpbb\karma\ucp\received_karma&sid=' . $this->sid);
		$link = $crawler->selectLink($this->lang('KARMA_REPORT', '', ''))->eq(0)->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/reportkarma')));
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());
		$form = $crawler->selectButton('submit')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('KARMA_REPORT_TEXT_EMPTY', $crawler->text());

		$form = $crawler->selectButton('submit')->form();
		$form['karma_report_text'] = 'This is a report_karma_test';
		$crawler = self::submit($form);
		$this->assertContainsLang('KARMA_SUCCESSFULLY_REPORTED', $crawler->text());
		$link = $crawler->selectLink($this->lang('RETURN_PAGE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'ucp.php')));
		$this->assertContainsLang('KARMA_ALREADY_REPORTED', $crawler->text());
	}

	public function test_close_karma_report()
	{
		$this->logout();
		$this->login();
		$this->admin_login();
		$crawler = $this->request('GET', 'mcp.php?i=\phpbb\karma\mcp\reported_karma&mode=reports&sid=' . $this->sid);
		$this->assertContainsLang('MCP_KARMA_REPORTS_OPEN_EXPLAIN', $crawler->text());
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());
		$form = $crawler->selectButton('action[close]')->form();
		$form['karma_report_id_list[0]']->tick();
		$crawler = self::submit($form);
		$this->assertContainsLang('CLOSE_REPORT_CONFIRM', $crawler->text());
		$form = $crawler->selectButton('confirm')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('KARMA_REPORT_CLOSED_SUCCESS', $crawler->text());
		$link = $crawler->selectLink($this->lang('RETURN_PAGE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'mcp.php')));
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());

		$crawler = $this->request('GET', 'mcp.php?i=\phpbb\karma\mcp\reported_karma&mode=reports_closed&sid=' . $this->sid);
		$this->assertContainsLang('MCP_KARMA_REPORTS_CLOSED_EXPLAIN', $crawler->text());
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());
	}

	public function test_delete_closed_karma_report()
	{
		$this->logout();
		$this->login();
		$this->admin_login();
		$crawler = $this->request('GET', 'mcp.php?i=\phpbb\karma\mcp\reported_karma&mode=reports_closed&sid=' . $this->sid);
		$this->assertContainsLang('MCP_KARMA_REPORTS_CLOSED_EXPLAIN', $crawler->text());
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());
		$form = $crawler->selectButton('action[delete]')->form();
		$form['karma_report_id_list[0]']->tick();
		$crawler = self::submit($form);
		$this->assertContainsLang('DELETE_REPORT_CONFIRM', $crawler->text());
		$form = $crawler->selectButton('confirm')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('KARMA_REPORT_DELETED_SUCCESS', $crawler->text());

		$link = $crawler->selectLink($this->lang('RETURN_PAGE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'mcp.php')));
		$this->assertContainsLang('NO_REPORTS', $crawler->text());
	}

	public function test_delete_open_karma_report()
	{
		$this->logout();
		$this->login();
		$this->admin_login();
		$crawler = $this->request('GET', 'mcp.php?i=\phpbb\karma\mcp\reported_karma&mode=reports&sid=' . $this->sid);
		$this->assertContainsLang('MCP_KARMA_REPORTS_OPEN_EXPLAIN', $crawler->text());
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());
		$form = $crawler->selectButton('action[delete]')->form();
		$form['karma_report_id_list[0]']->tick();
		$crawler = self::submit($form);
		$this->assertContainsLang('DELETE_REPORT_CONFIRM', $crawler->text());
		$form = $crawler->selectButton('confirm')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('KARMA_REPORT_DELETED_SUCCESS', $crawler->text());
		$link = $crawler->selectLink($this->lang('RETURN_PAGE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'mcp.php')));
		$this->assertContainsLang('NO_REPORTS', $crawler->text());

		$this->delete_test_user_post();
		$this->delete_test_user_post();
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$this->assertNotContains('Testing Subject 1', $crawler->filter('html')->text());
		$this->assertNotContains('Testing Subject 2', $crawler->filter('html')->text());

		$this->delete_given_karma();
	}

	protected function delete_test_user_post()
	{
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('DELETE_POST', '', ''))->eq(1)->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'posting.php?mode=delete')) ."&sid={$this->sid}");
		$form = $crawler->selectButton('Yes')->form();
		$form['delete_permanent']->tick();
		$crawler = self::submit($form);
	}

	protected function delete_given_karma()
	{
		$crawler = $this->request('GET', 'adm/index.php?i=\phpbb\karma\acp\main_module&mode=history&sid=' . $this->sid);
		$form = $crawler->selectButton('action[del_all]')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIRM_OPERATION', $crawler->text());
		$form = $crawler->selectButton('confirm')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('NO_ENTRIES', $crawler->text());
	}
}
