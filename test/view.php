<?php
/**
 * Configuration file for view container.
 */
return [
    "path" => [
        ANAX_APP_PATH . "/view",
        ANAX_INSTALL_PATH . "/view",
        ANAX_INSTALL_PATH . "/vendor/anax/view/view",
    ],
    
    "suffix" => ".php",

    "include" => [
        ANAX_INSTALL_PATH . "/vendor/anax/view/src/View/ViewHelperFunctions.php",
    ]
];
