<?php
/**
 * Configuration file for DI container.
 */
return [
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
