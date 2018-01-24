<?php
/**
 * Configuration file for DI container.
 */
return [
    "services" => [
        "navbar" => [
            "shared" => true,
            "callback" => function () {
                $navbar = new \Guni\Navbar\Navbar();
                $navbar->configure("navbar.php");
                $navbar->setDI($this);
                return $navbar;
            }
        ],
    ],
];
