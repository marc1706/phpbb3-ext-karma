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
	}

	public function test_karma_history()
	{
		$this->create_and_karma_post();
		$crawler = $this->request('GET', 'adm/index.php?i=\phpbb\karma\acp\main_module&mode=history&sid=' . $this->sid);

		$this->add_lang_ext('phpbb/karma', 'info_acp_karma');
		$this->assertContainsLang('ACP_KARMA_HISTORY', $crawler->text());
	}

	protected function create_and_karma_post()
	{
		$this->logout();
		$uid = $this->create_user('test_user');
		if (!$uid)
		{
			$this->markTestIncomplete('Unable to create test_user');
		}
		$this->login('test_user');

		$post = $this->create_post(2, 1, 'Testing Subject', 'This is a test post by test_user.', array());
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$this->assertContains('This is a test post by test_user.', $crawler->filter('html')->text());

		$this->logout();
		$this->login();
		$this->admin_login();

		$this->add_lang_ext('phpbb/karma', 'karma');
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('GIVEKARMA_POSITIVE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/')) ."&sid={$this->sid}");
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());

		$form = $crawler->selectButton('submit')->form();
		$form['karma_score']->select('1');
		$form['karma_comment'] = 'Positive Karma Comment';
		$crawler = self::submit($form);
		$link = $crawler->filter('a:contains("karma")')->attr('href');
		$crawler = self::request('GET', substr($link, strpos($link, 'viewtopic.')));
		$this->assertContains('Karma: +1', $crawler->filter('html')->text());
	}
}
