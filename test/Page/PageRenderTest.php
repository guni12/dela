<?php

namespace Guni\Page;

use \Anax\DI\DIInterface;
use \Guni\Page\PageRender;

/**
 * HTML Form elements.
 */
class PageRenderTest extends \PHPUnit_Framework_TestCase
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
        $page = new PageRender();
        $this->assertInstanceOf("\Guni\Page\PageRender", $page);

        $view = $this->di->get("view");
        $text = "Lite text";
        $data["title"] = "Titel";
        $arr = $this->di->get("view");

        $page->addViewContent($view, $text, "main", $data);
        $this->assertEquals($view, $arr);
    }
}