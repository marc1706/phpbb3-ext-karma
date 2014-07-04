<?php
/**
*
* @package phpBB Karma Testing
* @copyright (c) 2014 phpBB
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\karma\tests\karma;

// Include these files to make truncate_string() work in includes/manager.php
require_once(dirname(__FILE__) . '/../../../../../includes/utf/utf_tools.php');
require_once(dirname(__FILE__) . '/../../../../../includes/functions_content.php');

class report_karma_test extends \phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/karma.xml');
	}

	protected $karma_manager;

	protected $karma_report_model;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	static protected function setup_extensions()
	{
		return array('phpbb/karma');
	}

	public function setUp()
	{
		global $phpbb_root_path, $phpEx;

		parent::setUp();

		$this->db = $this->new_dbal();
		$this->config = new \phpbb\config\config(array());
		$this->cache = new \phpbb\cache\service(
			new \phpbb\cache\driver\null(),
			$this->config,
			$this->db,
			$phpbb_root_path,
			$phpEx
		);

		$this->container = new \phpbb\karma\tests\mock\container_builder();
		$this->dispatcher = new \phpbb\event\dispatcher($this->container);
		$this->template = new \phpbb\karma\tests\mock\template();
		$this->user = new \phpbb\karma\tests\mock\user();
		$this->helper = new \phpbb\karma\tests\mock\controller_helper();

		$this->karma_manager = new \phpbb\karma\includes\manager(
			array('karma.type.post' => array()),
			$this->cache,
			$this->container,
			$this->db,
			$this->dispatcher,
			$this->helper,
			$this->user,
			$phpbb_root_path,
			$phpEx,
			'phpbb_karma',
			'phpbb_karma_types'
		);
		$this->karma_report_model = new \phpbb\karma\includes\report_model(
			$this->db,
			$this->user,
			$this->karma_manager,
			'phpbb_karma_reports'
		);
		$this->container->set(
			'karma.type.post',
			new \phpbb\karma\includes\type\post(
				new \phpbb\karma\tests\mock\karma_auth(), $this->db, $this->user, $phpbb_root_path, $phpEx, 'phpbb_karma'
			)
		);
	}

	public function report_data()
	{
		// Basic test (should succeed)
		$basic_test = array(
			'reporter_id'			=> 1,
			'karma_report_text'		=> 'a',
			'karma_report_time'		=> time(),
			'item_id'				=> 1,
			'karma_type_name'		=> 'post',
			'giving_user_id'		=> 1,
			'karma_score'			=> 1,
		);

		// Big values (should succeed)
		$big_number = 1000000;
		$big_string = str_repeat('a', 4000);
		$big_values_test = array(
			'reporter_id'			=> $big_number,
			'karma_report_text'		=> $big_string,
			'karma_report_time'		=> pow(2, 31) - 1,
			'item_id'				=> $big_number,
			'karma_type_name'		=> 'post',
			'giving_user_id'		=> $big_number,
			'karma_score'			=> -128,
		);

		// Missing values (should succeed as the missing values are optional)
		$missing_values_test = array(
			'reporter_id'			=> 1,
			'karma_report_text'		=> 'a',
			'item_id'				=> 1,
			'karma_type_name'		=> 'post',
			'giving_user_id'		=> 1,
			'karma_score'			=> 1,
		);

		// Illegal values (\OutOfBoundsException expected)
		// These are all tried individually, with the basic test as a template
		$too_large_int = pow(2, 32);
		$illegal_values = array(
			'reporter_id'			=> array(-1, $too_large_int),
			'karma_report_time'		=> array($too_large_int),
		);

		// Combine the above test values into an array of data
		$return = array(
			array($basic_test, ''),
			array($big_values_test, ''),
			array($missing_values_test, ''),
		);
		foreach ($illegal_values as $field => $values)
		{
			$template = $basic_test;
			foreach ($values as $value)
			{
				$template[$field] = $value;
				$return[] = array($template, '\OutOfBoundsException');
			}
		}
		return $return;
	}

	/**
	 * @dataProvider report_data
	 */
	public function test_report_karma($karma_report, $expected_exception)
	{
		if (!empty($expected_exception))
		{
			$this->setExpectedException($expected_exception);
		}
		$this->karma_manager->store_karma($karma_report['karma_type_name'], $karma_report['item_id'], $karma_report['giving_user_id'], $karma_report['karma_score']);
		$karma_id = $this->get_karma_id($karma_report['item_id'], $karma_report['giving_user_id']);
		if (!isset($karma_report['karma_report_time']))
		{
			$this->karma_report_model->report_karma($karma_id, $karma_report['reporter_id'], $karma_report['karma_report_text']);
		}
		else
		{
			$this->karma_report_model->report_karma($karma_id, $karma_report['reporter_id'], $karma_report['karma_report_text'], $karma_report['karma_report_time']);
		}

		if (empty($expected_exception))
		{
			$this->assert_karma_report_row_exists($karma_report);
		}
	}

	protected function assert_karma_report_row_exists($row)
	{
		$sql = 'SELECT COUNT(*) AS num_rows FROM phpbb_karma_reports WHERE karma_id = ' . $this->get_karma_id($row['item_id'], $row['giving_user_id']);
		if ($row['karma_report_time'])
		{
			$sql .= ' AND karma_report_time = ' . $row['karma_report_time'];
		}
		$result = $this->db->sql_query($sql);
		$this->assertEquals(true, (bool) $result);
		$this->db->sql_freeresult($result);
	}

	protected function get_karma_id($item_id, $giving_user_id)
	{
		$result = $this->db->sql_query('
			SELECT karma_id
			FROM phpbb_karma
			WHERE item_id = ' . $item_id .
				' AND giving_user_id = ' . $giving_user_id
		);
		$karma_id = $this->db->sql_fetchfield('karma_id');
		$this->db->sql_freeresult($result);
		return $karma_id;
	}
}
