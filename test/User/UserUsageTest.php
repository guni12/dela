<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\User\User;

/**
 * HTML Form elements.
 */
class UserUsageTest extends \PHPUnit_Framework_TestCase
{
    public $di;

    /**
     * Setup before each testcase
     */
    public function setUp()
    {
        $this->di = new \Anax\DI\DIFactoryConfig("di.php");
    }


    public function testUsage()
    {
        $user = new User($this->di);
        $user2 = new User($this->di);
        $this->assertInstanceOf("\Guni\User\User", $user);
        $this->assertInstanceOf("\Guni\User\User", $user2);

        $user->setPassword("mumintrollet");
        $user2->setPassword("mumintrollet");
        $test2 = [$user->email, $user->password];

        //$this->assertEquals($user, $user2);
        //$this->assertContains('email', $user);
        $this->assertObjectHasAttribute('email', $user);
        //$this->assertArrayHasKey('email', $user);
        //$this->assertEmpty($test2);
        //$this->assertEquals($user->password, $user2->password);

    }
}