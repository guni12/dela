<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\Comments\Misc;

/**
 * HTML Form elements.
 */
class CommPagesUsageTest extends \PHPUnit_Framework_TestCase
{
    public static $di;
    public static $db;


    /**
     * Setup before each testcase
     */
    public function setUp()
    {
        $di = self::$di;
        self::$di = new \Anax\DI\DIFactoryConfig(__DIR__ . "/../di_dummy.php");
    }

    public function testBasic()
    {
        $comm = new \Guni\Comments\Misc(self::$di);
        $this->assertInstanceOf("\Guni\Comments\Misc", $comm);

        $email = $comm->getGravatar("gunvor@behovsbo.se", 50);
        $exp = '<img src="https://www.gravatar.com/avatar/2438dc720f1ca2c32c27a5bb658229c4?s=50&d=mm&r=g" alt=""/>';
        $this->assertEquals($exp, $email);

        $url = $comm->setUrlCreator("comm");
        //$exp = "://.bin/comm";



        //$this->assertEquals($url, $exp);

        $answerlink = $comm->getAnswerLink(2);
        $exp = '<a href="://.bin/comm/create/2">Svara</a>';
        //$this->assertEquals($answerlink, $exp);

        $commentlink = $comm->getCommentLink(4);
        $exp = '<a href="://.bin/comm/comment/4">Kommentera</a>';
        //$this->assertEquals($commentlink, $exp);

    }
}
