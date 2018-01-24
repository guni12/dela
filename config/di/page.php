<?php
/**
 * Configuration file for DI container.
 */
return [
    "services" => [
        "pageRender" => [
            "shared" => true,
            "callback" => function () {
                $obj = new \Guni\Page\PageRender();
                $obj->setDI($this);
                return $obj;
            }
        ],
        "errorController" => [
            "shared" => true,
            "callback" => function () {
                $obj = new \Guni\Page\ErrorController();
                $obj->setDI($this);
                return $obj;
            }
        ],
        "debugController" => [
            "shared" => true,
            "callback" => function () {
                $obj = new \Guni\Page\DebugController();
                $obj->setDI($this);
                return $obj;
            }
        ],
        "flatFileContentController" => [
            "shared" => true,
            "callback" => function () {
                $obj = new \Guni\Page\FlatFileContentController();
                $obj->setDI($this);
                return $obj;
            }
        ],
    ],
];
