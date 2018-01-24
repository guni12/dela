<?php
/**
 * Configuration file for DI container.
 */
return [
    "services" => [
        "userController" => [
            "shared" => true,
            "callback" => function () {
                $obj = new \Guni\User\UserController();
                $obj->setDI($this);
                return $obj;
            }
        ],
    ],
];
