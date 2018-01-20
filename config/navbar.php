<?php
/**
 * Config file for the navbar.
 */

return [
    "items" => [
        "home" => [
            "text" => "Hem",
            "route" => "comm/front",
        ],
        "ques" => [
            "text" => "Frågor",
            "route" => "comm",
        ],
        "tags" => [
            "text" => "Taggar",
            "route" => "comm/tags",
        ],
        "members" => [
            "text" => "Medlemmar",
            "route" => "user",
        ],
        "about" => [
            "text" => "Om",
            "route" => "about",
        ],
    ]
];
