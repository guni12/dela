<?php

namespace Guni\Page;

use \Anax\DI\DIInterface;
use \Guni\Navbar\Navbar;

/**
 * HTML Form elements.
 */
class NavbarTest extends \PHPUnit_Framework_TestCase
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
        $nav = new Navbar();
        $this->assertInstanceOf("\Guni\Navbar\Navbar", $nav);

        $res = $nav->getToLogin("create", "comm");
        $exp = '<li><a href = "create"><i class="fa fa-hand-o-right" aria-hidden="true"></i> Bli medlem</span></a></li><li><a href="comm"><span class="glyphicon glyphicon-log-in"></span> Logga in</a></li>';
        $this->assertEquals($exp, $res);

        $eod = $nav->getEOD("spans", "home", "links", "loginout");
        $exp2 = <<<EOD
<nav class="navbar">
<div class="container-fluid">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">spans</button>
<a class="navbar-brand" href="home">dELa</a>
</div>
<div class="collapse navbar-collapse" id="myNavbar">
    <ul class="nav navbar-nav">
        links
    </ul>
    <ul class="nav navbar-nav navbar-right">
        loginout   
    </ul>
</div>
</div>
</nav>
EOD;
        $this->assertEquals($exp2, $eod);
    }
}
