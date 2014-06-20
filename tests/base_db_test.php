<?php
/**
*
* @package phpBB Karma Testing
* @copyright (c) 2013 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\karma\tests;

class base_db_test extends \phpbb_database_test_case
{
	protected $db;

	static protected function setup_extensions()
	{
		return array('phpbb/karma');
	}

	public function setUp()
	{
		parent::setUp();

		global $db;
		$db = $this->db = $this->new_dbal();
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/config.xml');
	}

	public function test_database_connection()
	{
		$result = $this->db->sql_query('SELECT * FROM phpbb_config');
		$this->assertEquals($this->db->sql_fetchrowset($result), array());
	}
}
