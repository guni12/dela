<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\User\User;
use \Guni\User\UserController;
use \Guni\User\HTMLForm\UserLoginForm;


/**
 * Tests.
 */
class UserUsageTest extends \PHPUnit_Framework_TestCase
{
    public $di;


    /**
     * Testcase
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
        //$test2 = [$user->email, $user->password];

        $this->assertObjectHasAttribute('email', $user);
        //$this->assertArrayHasKey('email', $user);
        //$this->assertEmpty($test2);


        $control = new UserController();
        $this->assertInstanceOf("\Guni\User\UserController", $control);

        /*$stub = $this->createMock($user);

        $stub->method('getPostAdminCreateUser')
             ->willReturn('foo');

        $this->assertEquals('foo', $stub->getPostAdminCreateUser());*/

        //$loginDetails = array('user' => "Gunvor",'password' => 'password',);

        //$login = new UserLoginForm($this->di);
        $form = ["test"];

        $formmodel = $this->createMock('\Anax\HTMLForm\FormModel', array(), array($this->di, $form));

        $this->assertInstanceOf("\Anax\HTMLForm\FormModel", $formmodel);

    }
}
