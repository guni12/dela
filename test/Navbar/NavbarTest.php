<?php

namespace Guni\Page;

use \Anax\DI\DIInterface;
use \Guni\Navbar\Navbar;
use \Guni\Comments\Comm;

/**
 * HTML Form elements.
 */
class NavbarTest extends \PHPUnit_Framework_TestCase
{
    public static $di;
    public static $sess;
    public $nav;

    /**
     * Setup before each testcase
     */
    public function setUp()
    {
        self::$di = new \Anax\DI\DIFactoryConfig(__DIR__ . "/../di_dummy.php");
        self::$sess = self::$di->get("session");
        $this->nav = new Navbar();
    }



    public function testBasic()
    {
        $this->assertInstanceOf("\Guni\Navbar\Navbar", $this->nav);

        $res = $this->nav->getToLogin("create", "comm");
        $exp = '<li><a href = "create"><i class="fa fa-hand-o-right" aria-hidden="true"></i> Bli medlem</span></a></li><li><a href="comm"><span class="glyphicon glyphicon-log-in"></span> Logga in</a></li>';
        $this->assertEquals($exp, $res);

        $eod = $this->nav->getEOD("spans", "home", "links", "loginout");
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


    /**
    *
    *
    */
    public function testLinktext()
    {
        $inlogg = [
            'loggedin' => true,
            'id' => 1,
            'acronym' => 'guni',
            'isadmin' => 1,
            'email' => 'gunvor@behovsbo.se'
        ];
        self::$sess->set('user', $inlogg);
        $test = self::$sess->get("user");

        $this->assertEquals($test, $inlogg);
        
        $comm = new Comm(self::$di);
        $grav = "<img src='" . $comm->getGravatar($test['email']) . "' />";
        $url = self::$di->get("url");


        $res = $this->nav->getIsLoggedin($url, 'a href="://.bin/user/update', $test, $grav);
        $exp = '<li><a href="a href="://.bin/user/update/1"><span class="userupdate em08"><span class="userupdatetext">Uppdatera guni\'s uppgifter</span>';
        $exp .= "<img src='https://www.gravatar.com/avatar/2438dc720f1ca2c32c27a5bb658229c4?s=20&d=mm&r=g' />";
        $exp .= '</span></a></li><li><a href="://.bin/user/logout"><span class="glyphicon glyphicon-log-out"></span> Logga ut</a></li>';
        $this->assertEquals($res, $exp);
        
    }
}
