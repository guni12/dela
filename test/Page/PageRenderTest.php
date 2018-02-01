<?php

namespace Guni\Page;

use \Anax\DI\DIInterface;
use \Guni\Page\PageRender;

/**
 * HTML Form elements.
 */
class PageRenderTest extends \PHPUnit_Framework_TestCase
{
    public static $di;
    public static $sess;    

    /**
     * Setup before each testcase
     */
    public function setUp()
    {
        self::$di = new \Anax\DI\DIFactoryConfig(__DIR__ . "/../di_dummy.php");
        self::$sess = self::$di->get("session");
    }



    public function testBasic()
    {
        $page = new PageRender();
        $this->assertInstanceOf("\Guni\Page\PageRender", $page);

        $view = self::$di->get("view");
        $text = "Lite text";
        $data["title"] = "Titel";
        $arr = self::$di->get("view");

        $page->addViewContent($view, $text, "main", $data);
        $this->assertEquals($view, $arr);
    }
}
