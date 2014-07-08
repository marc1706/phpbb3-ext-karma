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
	}

	public function test_karma()
	{
		$this->logout();
		$uid = $this->create_user('karma_giving_user');
		if (!$uid)
		{
			$this->markTestIncomplete('Unable to create karma_giving_user');
		}
		$this->login('karma_giving_user');
		$crawler = $this->request('GET', 'index.php?sid=' . $this->sid);
	}
}
