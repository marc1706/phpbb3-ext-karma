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
class karma_test extends \phpbb_functional_test_case
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

		$this->add_lang_ext('phpbb/karma', 'karma');
	}

	protected function karma_create_post()
	{
		$this->logout();
		$uid = $this->create_user('karma_user');
		if (!$uid)
		{
			$this->markTestIncomplete('Unable to create karma_user');
		}
		$this->login('karma_user');

		$post = $this->create_post(2, 1, 'Testing Subject', 'This is a test post by karma_user.', array());
	}

	public function test_givekarma_positive()
	{
		$this->karma_create_post();
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

		$this->assertContainsLang('KARMA_SUCCESSFULLY_GIVEN', $crawler->text());
		$link = $crawler->selectLink($this->lang('KARMA_VIEW_ITEM', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'viewtopic.')));
		$this->assertContains('Karma: +1', $crawler->filter('html')->text());
	}

	public function test_givekarma_undo()
	{
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('GIVEKARMA_POSITIVE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/')) ."&sid={$this->sid}");
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());

		$form = $crawler->selectButton('submit')->form();
		$form['karma_score']->select('0');
		$crawler = self::submit($form);

		$this->assertContainsLang('KARMA_SUCCESSFULLY_DELETED', $crawler->text());
		$link = $crawler->filter('a:contains("karma")')->attr('href');
		$crawler = self::request('GET', substr($link, strpos($link, 'viewtopic.')));
		$this->assertContains('Karma: 0', $crawler->filter('html')->text());
	}

	public function test_givekarma_negative()
	{
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('GIVEKARMA_NEGATIVE', '', ''))->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'app.php/')) ."&sid={$this->sid}");
		$this->assertContains('Testing Subject', $crawler->filter('html')->text());

		$form = $crawler->selectButton('submit')->form();
		$form['karma_score']->select('-1');
		$form['karma_comment'] = 'Negative Karma Comment';
		$crawler = self::submit($form);

		$this->assertContainsLang('KARMA_SUCCESSFULLY_GIVEN', $crawler->text());
		$link = $crawler->filter('a:contains("karma")')->attr('href');
		$crawler = self::request('GET', substr($link, strpos($link, 'viewtopic.')));
		$this->assertContains('Karma: -1', $crawler->filter('html')->text());

		$this->delete_karma_post();
	}

	protected function delete_karma_post()
	{
		$crawler = self::request('GET', "viewtopic.php?t=1&sid={$this->sid}");
		$link = $crawler->selectLink($this->lang('DELETE_POST', '', ''))->eq(1)->link()->getUri();
		$crawler = self::request('GET', substr($link, strpos($link, 'posting.php?mode=delete')) ."&sid={$this->sid}");
		$form = $crawler->selectButton('Yes')->form();
		$form['delete_permanent']->tick();
		$crawler = self::submit($form);
	}
}
