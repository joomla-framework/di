<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Github\Tests;

use Joomla\Github\Package\Users\Followers;
use Joomla\Registry\Registry;

/**
 * Test class for Emails.
 *
 * @since  ¿
 */
class FollowersTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    Registry  Options for the GitHub object.
	 * @since  ¿
	 */
	protected $options;

	/**
	 * @var    \PHPUnit_Framework_MockObject_MockObject  Mock client object.
	 * @since  ¿
	 */
	protected $client;

	/**
	 * @var    \Joomla\Http\Response  Mock response object.
	 * @since  ¿
	 */
	protected $response;

	/**
	 * @var Followers
	 */
	protected $object;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.3
	 */
	protected $errorString = '{"message": "Generic Error"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @since   ¿
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->options  = new Registry;
		$this->client   = $this->getMock('\\Joomla\\Github\\Http', array('get', 'post', 'delete', 'patch', 'put'));
		$this->response = $this->getMock('\\Joomla\\Http\\Response');

		$this->object = new Followers($this->options, $this->client);
	}

	/**
	 * Tests the  method
	 *
	 * @return  void
	 */
	public function testGetList()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/user/followers')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getList(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the  method
	 *
	 * @return  void
	 */
	public function testGetListWithUser()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/users/joomla/followers')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getList('joomla'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the  method
	 *
	 * @return  void
	 */
	public function testGetListFollowedBy()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/user/following')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getListFollowedBy(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the  method
	 *
	 * @return  void
	 */
	public function testGetListFollowedByWithUser()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/users/joomla/following')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getListFollowedBy('joomla'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the  method
	 *
	 * @return  void
	 */
	public function testCheck()
	{
		$this->response->code = 204;
		$this->response->body = true;

		$this->client->expects($this->once())
			->method('get')
			->with('/user/following/joomla')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->check('joomla'),
			$this->equalTo($this->response->body)
		);
	}

	/**
	 * Tests the  method
	 *
	 * @return  void
	 */
	public function testCheckNo()
	{
		$this->response->code = 404;
		$this->response->body = false;

		$this->client->expects($this->once())
			->method('get')
			->with('/user/following/joomla')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->check('joomla'),
			$this->equalTo($this->response->body)
		);
	}

	/**
	 * Tests the  method
	 *
	 * @return  void
	 *
	 * @expectedException \UnexpectedValueException
	 */
	public function testCheckUnexpected()
	{
		$this->response->code = 666;
		$this->response->body = false;

		$this->client->expects($this->once())
			->method('get')
			->with('/user/following/joomla')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->check('joomla'),
			$this->equalTo($this->response->body)
		);
	}

	/**
	 * Tests the  method
	 *
	 * @return  void
	 */
	public function testFollow()
	{
		$this->response->code = 204;
		$this->response->body = '';

		$this->client->expects($this->once())
			->method('put')
			->with('/user/following/joomla')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->follow('joomla'),
			$this->equalTo($this->response->body)
		);
	}

	/**
	 * Tests the  method
	 *
	 * @return  void
	 */
	public function testUnfollow()
	{
		$this->response->code = 204;
		$this->response->body = '';

		$this->client->expects($this->once())
			->method('delete')
			->with('/user/following/joomla')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->unfollow('joomla'),
			$this->equalTo($this->response->body)
		);
	}
}
