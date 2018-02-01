<?php

namespace Guni\Comments;

use \Anax\DI\DIInterface;

/**
 * Helper for html-code
 */
class Taglinks
{
    protected $di;
    protected $misc;

    /**
     * Constructor injects with DI container and the id to update.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
        $this->misc = new Misc($di);
    }


    /**
     * Returns all text for the view
     *
     * @return string htmlcode
     */
    public function getHTML()
    {
        $base = $this->misc->setUrlCreator("comm/tags/");
        $elcar = $base . "/elcar";
        $safety = $base . "/safety";
        $light = $base . "/light";
        $heat = $base . "/heat";

        $html = "";
        $html .= '<table class = "member"><tr>';
        $html .= '<th class = "elcar"><a href = "' . $elcar . '">Elbil</a></th><th class = "safety"><a href = "' . $safety . '">Säkerhet</a></th><th class = "light"><a href = "' . $light . '">Belysning</a></th><th class = "heat"><a href = "' . $heat . '">Värme</a></th></tr>';
        $html .= '<tr><td>Text om elbilar</td>';
        $html .= '<td>Text om Säkerhet</td>';
        $html .= '<td>Text om belysning</td>';
        $html .= '<td>Text om Värme</td></tr>';
        $html .= '</table>';
        $html .= "<br /><br />Saknar du någon tagg? Hör av dig till admin.";

        return $html;
    }
}
