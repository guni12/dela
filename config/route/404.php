<?php

return [
    "routes" => [
        [
            "info" => "Catch all and send 404.",
            "requestMethod" => null,
            "path" => null,
            "callable" => ["errorController", "page404"],
        ],
    ]
];
