<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\User\User;
use \Guni\User\UserHelp;
use \Guni\User\HTMLForm\UserLoginForm;


/**
 * Tests.
 */
class UserUsageTest extends \PHPUnit_Framework_TestCase
{
    public static $di;
    public static $db;
    public static $sess;
    public $userhelp;
    public $person;


    /**
     * Testcase
     */
    public function setUp()
    {
        self::$di = new \Anax\DI\DIFactoryConfig(__DIR__ . "/../di_dummy.php");
        self::$sess = self::$di->get("session");
        $this->userhelp = new UserHelp(self::$di);
        $this->person = null;
    }


    public function testUsage()
    {
        $user = new User(self::$di);
        $user2 = new User(self::$di);
        $this->assertInstanceOf("\Guni\User\User", $user);
        $this->assertInstanceOf("\Guni\User\User", $user2);

        $user->setPassword("mumintrollet");
        $user2->setPassword("mumintrollet");

        $this->assertObjectHasAttribute('email', $user);


        $control = new UserHelp(self::$di);
        $this->assertInstanceOf("\Guni\User\UserHelp", $control);

        /*$stub = $this->createMock($user);

        $stub->method('getPostAdminCreateUser')
             ->willReturn('foo');*/

        $form = ["test"];

        $formmodel = $this->createMock('\Anax\HTMLForm\FormModel', array(), array(self::$di, $form));

        $this->assertInstanceOf("\Anax\HTMLForm\FormModel", $formmodel);
    }


    /**
    *
    * Create a User
    */
    public function makeUser()
    {
        self::$db = self::$di->get("db");
        self::$db->connect();

        self::$db->createTable(
            "user",
            [
                "id" => ["INTEGER"],
                "acronym" => ["VARCHAR"],
                "password" => ["VARCHAR"],
                "email" => ["VARCHAR"],
                "profile" => ["VARCHAR"],
                "isadmin" => ["INTEGER"],
                "created" => ["DATETIME"],
                "updated" => ["DATETIME"],
                "deleted" => ["DATETIME"],
            ]
        )->execute();

        $create = new CreateUserForm(self::$di, null);
        $now = date("Y-m-d H:i:s");

        $user = new User();
        $user->setDb(self::$db);
        $user->acronym = 'adam';
        $user->email = 'adam@annan.se';
        $user->profile = 'Aderö';
        $user->setPassword('doe');
        $user->created = $now;
        $user->save();

        $findPerson = new User(self::$di);
        $findPerson->setDb(self::$db);
        $compare = $findPerson->find("acronym", 'adam');
        $this->person = $findPerson->find("profile", 'Aderö');

        $this->assertEquals($user->acronym, $compare->acronym);
        $this->assertEquals($user->profile, $compare->profile);
        $this->assertArrayHasKey('email', $user);
        $this->assertEmpty($user->updated);
    }


    /**
    *
    *
    */
    public function testUserHelper()
    {
        $res = $this->userhelp->getIsAnswer(10, 1);
        $exp = null;
        $this->assertEquals($res, $exp);

        $res2 = $this->userhelp->getIsComment(10, 1);
        $exp2 = 10;
        $this->assertEquals($res2, $exp2);

        $res3 = $this->userhelp->getName('safety');
        $exp3 = "Säkerhet";
        $this->assertEquals($res3, $exp3);
    }

}
