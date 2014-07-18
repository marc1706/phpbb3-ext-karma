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
	}

	protected function create_and_karma_post($user_name)
	{
		$this->login();
		$this->admin_login();
		$post = $this->create_post(2, 1, 'Testing Subject', 'This is a test post by admin as test_user.', array());

		$this->logout();
		$uid = $this->create_user($user_name);
		if (!$uid)
		{
			$this->markTestIncomplete('Unable to create test_user');
		}
		$this->login($user_name);

		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('GIVEKARMA_POSITIVE', '', ''))->eq(1)->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/')) ."&sid={$this->sid}");
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());

		$form = $crawler->selectButton('submit')->form();
		$form['karma_score']->select('1');
		$form['karma_comment'] = 'Positive Karma Comment';
		$crawler = self::submit($form);
	}

	public function test_received_karma()
	{
		$this->create_and_karma_post('test_report_user');
		$this->logout();
		$this->login();
		$this->admin_login();
		$crawler = $this->request('GET', 'ucp.php?i=\phpbb\karma\ucp\received_karma&sid=' . $this->sid);
		$this->assertContainsLang('UCP_RECEIVED_KARMA', $crawler->text());

		$link = $crawler->selectLink($this->lang('KARMA_REPORT', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/reportkarma')));
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());
		$form = $crawler->selectButton('submit')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('KARMA_REPORT_TEXT_EMPTY', $crawler->text());

		$form = $crawler->selectButton('submit')->form();
		$form['karma_report_text'] = 'This is a report_karma_test';
		$crawler = self::submit($form);
		$this->assertContainsLang('KARMA_SUCCESSFULLY_REPORTED', $crawler->text());
	}
}