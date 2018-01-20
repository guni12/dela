<?php

namespace Guni\Navbar;

use \Anax\DI\InjectionAwareInterface;
use \Anax\DI\InjectionAwareTrait;

/**
 * Navbar to generate HTML for a navbar from a configuration array.
 */
class Navbar implements
    \Anax\Common\ConfigureInterface,
    //\Anax\Common\AppInjectableInterface,
    InjectionAwareInterface
{
    use InjectionAwareTrait, \Anax\Common\ConfigureTrait;
    // \Anax\Common\AppInjectableTrait;

    private $currentUrl;
    private $htmlNavbar;


    /**
     * Get HTML for the navbar.
     *
     * @return string as HTML with the navbar.
     */
    public function getHTML()
    {
        $url = $this->di->get("url");
        $req = $this->di->get("request");
        $path = $req->getRoute();

        $session = $this->di->get("session");
        $sess = $session->get('user');

        $comm = $this->di->get("commController");
        $grav = $comm->getGravatar($sess['email']);
        $grav = "<img src='" . $grav . "' />";

        $span = '<span class="icon-bar"></span>';
        $spans = $span . $span . $span;

        $links = "";
        $tail = '"><a href="';
        $welcome = "";

        $this->setUrlCreator($this->config['items']['home']['route']);
        $home = $this->htmlNavbar;

        $navtext = "Logga in";
        $navpath = call_user_func([$url, "create"], "user/login");
        $create = call_user_func([$url, "create"], "user/create");
        $update = call_user_func([$url, "create"], "user/update");

        $loginout = '<li><a href = "' . $create . '">Bli medlem</span></a> ' . $welcome . '</li>';
        $loginout .= '<li><a href="' . $navpath . '"><span class="glyphicon glyphicon-log-in">';
        $loginout .= '</span> ' . $navtext . '</a></li>';

        if ($sess) {
            $navtext = "Logga ut";
            $navpath = call_user_func([$url, "create"], "user/logout");
            $welcome = $sess['acronym'];

            $loginout = '<li><a href="' . $update . '/' . $sess['id'] . '"><span class="userupdate"><span class="userupdatetext">Ändra ' . $sess['acronym'] . ' profil</span>' . $grav . '</span></a></li>';
            $loginout .= '<li><a href="' . $navpath . '"><span class="glyphicon glyphicon-log-out">';
            $loginout .= '</span> ' . $navtext . '</a></li>';
        }

        foreach ($this->config['items'] as $val) {
            $this->setUrlCreator($val['route']);
            $navtext = $val['text'];
            
            if ($val['route'] == $path) {
                $class = "active";
            } else {
                $class = "";
            }

            if ($val['route'] == "user/login") {
                $class .= " login";
            }
            
            $links .= '<li class="' . $class . $tail . $this->htmlNavbar . '">' . $navtext . '</a></li>';
        }
        
        $navbar = <<<EOD
<nav class="navbar">
<div class="container-fluid">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">{$spans}</button>
<a class="navbar-brand" href="{$home}">dELa</a>
</div>
<div class="collapse navbar-collapse" id="myNavbar">
    <ul class="nav navbar-nav">
        {$links}
    </ul>
    <ul class="nav navbar-nav navbar-right">
        {$loginout}   
    </ul>
</div>
</div>
</nav>
EOD;
        return
        $navbar;
    }

    /**
     * Sets the current route.
     *
     * @param string $route the current route.
     *
     * @return void
     */
    public function setCurrentRoute($route)
    {
        $this->currentUrl = $route;
    }

    /**
     * Sets the callable to use for creating routes.
     *
     * @param callable $urlCreate to create framework urls.
     *
     * @return void
     */
    public function setUrlCreator($route)
    {
        $url = $this->di->get("url");
        $this->htmlNavbar = call_user_func([$url, "create"], $route);
    }
}
