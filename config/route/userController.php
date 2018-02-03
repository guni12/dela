<?php
/**
 * Routes for user controller.
 */
return [
    "mount" => "user",
    "routes" => [
        [
            "info" => "User Controller index.",
            "requestMethod" => "get",
            "path" => "",
            "callable" => ["userController", "getIndex"],
        ],
        [
            "info" => "Login a user.",
            "requestMethod" => "get|post",
            "path" => "login",
            "callable" => ["userController", "getPostLogin"],
        ],
        [
            "info" => "Logout a user.",
            "requestMethod" => "get|post",
            "path" => "logout",
            "callable" => ["userController", "getPostLogout"],
        ],
        [
            "info" => "Create a user.",
            "requestMethod" => "get|post",
            "path" => "create",
            "callable" => ["userController", "getPostCreateUser"],
        ],
        [
            "info" => "Update a user.",
            "requestMethod" => "get|post",
            "path" => "update/{id:digit}",
            "callable" => ["userController", "getPostUpdateUser"],
        ],

        [
            "info" => "Delete a user.",
            "requestMethod" => "get|post",
            "path" => "delete/{id:digit}",
            "callable" => ["userController", "getPostDeleteUser"],
        ],

        [
            "info" => "Delete a user.",
            "requestMethod" => "get|post",
            "path" => "view-one/{id:digit}",
            "callable" => ["userController", "getPostOneUser"],
        ],
    ]
];
