<?php
/**
*
* @package phpBB Karma Testing
* @copyright (c) 2013 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\karma\tests\test_framework;

abstract class karma_database_test_case extends \phpbb_database_test_case
{
	protected $db;

	public function setUp()
	{
		parent::setUp();

		global $db;
		$db = $this->db = $this->new_dbal();
	}

	protected function create_connection_manager($config)
	{
		return new \phpbb\karma\tests\test_framework\karma_database_test_connection_manager($config);
	}

	public function get_database_config()
	{
		$config = \phpbb\karma\tests\test_framework\karma_test_case_helpers::get_test_config();

		if (!isset($config['dbms']))
		{
			$this->markTestSkipped('Missing test_config.php: See first error.');
		}

		return $config;
	}

	public function get_test_case_helpers()
	{
		if (!$this->test_case_helpers)
		{
			$this->test_case_helpers = new \phpbb\karma\tests\test_framework\karma_test_case_helpers($this);
		}

		return $this->test_case_helpers;
	}
}
