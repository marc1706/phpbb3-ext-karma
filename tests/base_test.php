<?php
/**
*
* @package phpBB Karma Testing
* @copyright (c) 2013 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\karma\tests;

class base_test extends \phpbb\karma\tests\test_framework\karma_test_case
{
	public function test_true()
	{
		$this->assertTrue(true);
	}

	public function test_false()
	{
		$this->assertFalse(false);
	}
}
