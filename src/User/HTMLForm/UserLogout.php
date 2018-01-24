<?php

namespace Guni\User\HTMLForm;

use \Anax\DI\DIInterface;
use \Guni\User\User;

/**
 * Example of FormModel implementation.
 */
class UserLogout
{
    protected $di;


    /**
     * Constructor injects with DI container.
     *
     * @param Anax\DI\DIInterface $di a service container
     */
    public function __construct(DIInterface $di)
    {
        $this->di = $di;
    }

    public function getHTML()
    {
        $session = $this->di->get("session");
        $sess = $session->get("user");
        $who = isset($sess['acronym']) ? $sess['acronym'] : "";

        $session->delete('user');
        $text = "Användaren " . $who . " loggade ut.";
        return $text;
    }
}
