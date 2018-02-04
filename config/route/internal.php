<?php
 return [
     "routes" => [
         [
             "info" => "403 Forbidden.",
             "internal" => true,
             "path" => "403",
             "callable" => ["errorController", "page403"],
         ],
         [
             "info" => "404 Page not found.",
             "internal" => true,
             "path" => "404",
             "callable" => ["errorController", "page404"],
         ],
         [
             "info" => "500 Internal Server Error.",
             "internal" => true,
             "path" => "500",
             "callable" => ["errorController", "page500"],
         ],
     ]
 ];
