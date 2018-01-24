<?php

use \Anax\Route\Router;

/**
 * Configuration file for routes.
 * "mode" => Router::DEVELOPMENT, // default, verbose execeptions
 * "mode" => Router::PRODUCTION,  // exceptions turn into 500
 * route 404 always last
 */
return [
    "routeFiles" => [
        [
            "mount" => null,
            "file" => __DIR__ . "/route/internal.php",
        ],
        [
            "mount" => "debug",
            "file" => __DIR__ . "/route/debug.php",
        ],
        [
            "mount" => null,
            "file" => __DIR__ . "/route/flat-file-content.php",
        ],
        [
            "mount" => null,
            "sort" => 999,
            "file" => __DIR__ . "/route/404.php",
        ],
    ],

];
