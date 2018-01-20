<?php
/**
 * Configuration file for DI container.
 */
return [

    // Services to add to the container.
    "services" => [
        "commController" => [
            "shared" => true,
            "callback" => function () {
                $obj = new \Guni\Comments\CommController();
                $obj->setDI($this);
                return $obj;
            }
        ],
    ],
];
