<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;
use \Guni\Comments\Comm;
use \Guni\Comments\CommController;

/**
 * HTML Form elements.
 */
class CommControllerUsageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup before each testcase
     */
    public function setUp()
    {
        $this->di = new \Anax\DI\DIFactoryConfig("di.php");
    }

        public function testBasic()
    {
        $comm = new \Guni\Comments\CommController();
        $this->assertInstanceOf("\Guni\Comments\CommController", $comm);

        $email = $comm->getGravatar("gunvor@behovsbo.se", 50);
        $exp = "https://www.gravatar.com/avatar/2438dc720f1ca2c32c27a5bb658229c4?s=50&d=mm&r=g";
        $this->assertEquals($exp, $email);
    }
}