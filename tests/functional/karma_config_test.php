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
class karma_config_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('phpbb/karma');
	}

	public function setUp()
	{
		parent::setUp();

		$this->add_lang_ext('phpbb/karma', 'info_acp_karma');
		$this->add_lang_ext('phpbb/karma', 'karma');
	}

	public function test_karma_history()
	{
		$this->login();
		$this->admin_login();
		$crawler = $this->request('GET', 'adm/index.php?i=\phpbb\karma\acp\main_module&mode=config&sid=' . $this->sid);
		$this->assertContainsLang('ACP_KARMA_CONFIG', $crawler->text());

		$this->create_test_user();
	}

	public function test_karma_minimum_config()
	{
		$this->karma_post_success();

		$this->set_karma_minimum(2);
		$this->logout();
		$this->login('test_user');
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('GIVEKARMA_POSITIVE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/')) ."&sid={$this->sid}");
		$this->assertContainsLang('INSUFFICIENT_KARMA', $crawler->text());

		$this->set_karma_minimum(0);
	}

	public function test_post_minimum_config()
	{
		$this->karma_post_success();

		$this->set_post_minimum(1);
		$this->logout();
		$this->login('test_user');
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('GIVEKARMA_POSITIVE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/')) ."&sid={$this->sid}");
		$this->assertContainsLang('INSUFFICIENT_POSTS', $crawler->text());

		$this->set_post_minimum(0);
	}

	public function test_karma_per_day_config()
	{
		$this->karma_post_success();

		$this->set_karma_per_day(1);
		$this->logout();
		$this->login('test_user');
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('GIVEKARMA_NEGATIVE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/')) ."&sid={$this->sid}");
		$this->assertContainsLang('KARMA_PER_DAY_LIMIT_REACHED', $crawler->text());

		$this->set_karma_per_day(0);

		$this->delete_test_karma();
	}

	protected function create_test_user()
	{
		$this->logout();
		$uid = $this->create_user('test_user');
		if (!$uid)
		{
			$this->markTestIncomplete('Unable to create test_user');
		}
		$this->login('test_user');
	}

	protected function karma_post_success()
	{
		$this->logout();
		$this->login('test_user');
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('GIVEKARMA_POSITIVE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/')) ."&sid={$this->sid}");
		$this->assertContains('Welcome to phpBB3', $crawler->filter('html')->text());

		$form = $crawler->selectButton('submit')->form();
		$form['karma_score']->select('1');
		$form['karma_comment'] = 'Positive Karma Comment';
		$crawler = self::submit($form);
	}

	protected function set_karma_minimum($karma_minimum)
	{
		$this->logout();
		$this->login();
		$this->admin_login();
		$crawler = $this->request('GET', 'adm/index.php?i=\phpbb\karma\acp\main_module&mode=config&sid=' . $this->sid);
		$form = $crawler->selectButton('submit')->form();
		$form['config[karma_minimum]'] = $karma_minimum;
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->text());
	}

	protected function set_post_minimum($post_minimum)
	{
		$this->logout();
		$this->login();
		$this->admin_login();
		$crawler = $this->request('GET', 'adm/index.php?i=\phpbb\karma\acp\main_module&mode=config&sid=' . $this->sid);
		$form = $crawler->selectButton('submit')->form();
		$form['config[post_minimum]'] = $post_minimum;
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->text());
	}

	protected function set_karma_per_day($karma_per_day)
	{
		$this->logout();
		$this->login();
		$this->admin_login();
		$crawler = $this->request('GET', 'adm/index.php?i=\phpbb\karma\acp\main_module&mode=config&sid=' . $this->sid);
		$form = $crawler->selectButton('submit')->form();
		$form['config[karma_per_day]'] = $karma_per_day;
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->text());
	}

	protected function delete_test_karma()
	{
		$crawler = $this->request('GET', 'adm/index.php?i=\phpbb\karma\acp\main_module&mode=history&sid=' . $this->sid);
		$this->assertContains('test_user', $crawler->filter('html')->text());
		$form = $crawler->selectButton('action[del_all]')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIRM_OPERATION', $crawler->text());
		$form = $crawler->selectButton('confirm')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('NO_ENTRIES', $crawler->text());
	}
}
