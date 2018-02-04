<?php

namespace Guni\Page;

use \Anax\DI\InjectionAwareInterface;
use \Anax\DI\InjectionAwareTrait;

/**
 * A default page rendering class.
 */
class PageRender implements PageRenderInterface, InjectionAwareInterface
{
    use InjectionAwareTrait;


    public function redirect($newpage)
    {
        $response = $this->di->get("response");
        $url = $this->di->get("url");
        $response->redirect($url->create($newpage));
    }


    public function addViewContent($arr)
    {
        $arr[0]->add("view/header", [], "header");
        
        $arr[0]->add("view/navbar", [
            "navbar" => $arr[4]->getHTML()
        ], "navbar", 0);

        $arr[0]->add("view/footer", [
            "footeradd" => ""
        ], "footer", 1);
        $arr[0]->add("default1/article", [
                "content" => $arr[1]
            ], $arr[2], 0);
        $arr[0]->add("view/layout", $arr[3], "layout");
    }



    /**
     * Render a standard web page using a specific layout.
     * @SuppressWarnings("exit")
     * @param array   $data   variables to expose to layout view.
     * @param integer $status code to use when delivering the result.
     *
     * @return void
     */
    public function renderPage($text, $meta = null, $status = 200)
    {
        $text = is_array($text) && isset($text['content']) ? $text['content'] : (isset($text['form']) ? $text['form'] : '<div class="col-lg-12 col-sm-12 col-xs-12">' . $text . '</div>');

        $data = array();
        $data["stylesheets"] = isset($meta["stylesheets"]) ? $meta["stylesheets"] : ["css/style.css"];
        $data["title"] = isset($meta["title"]) ? $meta["title"] : "dELa";
        $region = isset($meta['region']) ? $meta['region'] : "main";

        $view = $this->di->get("view");

        $navbar = $this->di->get("navbar");
        $arr = [$view, $text, $region, $data, $navbar];

        $this->addViewContent($arr);
        $body = $view->renderBuffered("layout");
        $this->di->get("response")->setBody($body)
                                  ->send($status);
        exit;
    }
}
