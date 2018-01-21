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
     * Sets the callable to use for creating routes.
     *
     * @param obj $url - di_connection
     * @param string $update - pathbase
     * @param obj $sess - session_info
     * @param string $grav - gravator_htmltext
     *
     * @return string $loginout - htmltext
     */
    public function getIsLoggedin($url, $update, $sess, $grav)
    {
        $navtext = "Logga ut";
        $navpath = call_user_func([$url, "create"], "user/logout");

        $loginout = '<li><a href="' . $update . '/' . $sess['id'] . '"><span class="userupdate"><span class="userupdatetext">Ändra ' . $sess['acronym'] . ' profil</span>' . $grav . '</span></a></li>';
        $loginout .= '<li><a href="' . $navpath . '"><span class="glyphicon glyphicon-log-out">';
        $loginout .= '</span> ' . $navtext . '</a></li>';
        return $loginout;
    }

    /**
     * @param obj $url - di_connection
     * @param obj $val - navpath_info
     *
     * @return string $link - htmltext
     */
    public function getNavLink($url, $val)
    {
        $req = $this->di->get("request");
        $path = $req->getRoute();
        $htmlNavbar = call_user_func([$url, "create"], $val['route']);
        $navtext = $val['text'];
        $tail = '"><a href="';
        
        if ($val['route'] == $path) {
            $class = "active";
        } else {
            $class = "";
        }

        if ($val['route'] == "user/login") {
            $class .= " login";
        }
        $link = '<li class="' . $class . $tail . $htmlNavbar . '">' . $navtext . '</a></li>';
        return $link;
    }


    /**
     * @param string $spans - htmlcode for hamburger-icon
     * @param string $home - htmlcode for active link
     * @param string $links - htmlcode for navigation
     * @param string $loginout - htmlcode for login-paths
     *
     * @return string $navbar - all the navbar htmltext
     */
    public function getEOD($spans, $home, $links, $loginout)
    {
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
     * Get HTML for the navbar.
     *
     * @return string as HTML with the navbar.
     */
    public function getHTML()
    {
        $session = $this->di->get("session");
        $sess = $session->get('user');

        $url = $this->di->get("url");
        $home = call_user_func([$url, "create"], $this->config['items']['home']['route']);
        $navpath = call_user_func([$url, "create"], "user/login");
        $create = call_user_func([$url, "create"], "user/create");
        $update = call_user_func([$url, "create"], "user/update");        
        if ($sess['isadmin'] == 1) {
            $update = call_user_func([$url, "create"], "user/adminupdate");
        }

        $comm = $this->di->get("commController");
        $grav = $comm->getGravatar($sess['email']);
        $grav = "<img src='" . $grav . "' />";

        $span = '<span class="icon-bar"></span>';
        $spans = $span . $span . $span;

        $links = "";
        $navtext = "Logga in";

        $loginout = '<li><a href = "' . $create . '">Bli medlem</span></a></li>';
        $loginout .= '<li><a href="' . $navpath . '"><span class="glyphicon glyphicon-log-in">';
        $loginout .= '</span> ' . $navtext . '</a></li>';

        if ($sess) {
            $loginout = $this->getIsLoggedin($url, $update, $sess, $grav);
        }

        foreach ($this->config['items'] as $val) {
            $links .= $this->getNavLink($url, $val);
        }

        $navbar = $this->getEOD($spans, $home, $links, $loginout);
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
}
