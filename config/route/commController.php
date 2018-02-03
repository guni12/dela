<?php
/**
 * Routes for controller.
 */
return [
    "mount" => "comm",
    "routes" => [
        [
            "info" => "Controller index.",
            "requestMethod" => "get",
            "path" => "",
            "callable" => ["commController", "getIndex"],
        ],
        [
            "info" => "Create an item.",
            "requestMethod" => "get|post",
            "path" => "create",
            "callable" => ["commController", "getPostCreateItem"],
        ],
        [
            "info" => "Create a comment.",
            "requestMethod" => "get|post",
            "path" => "create/{id:digit}",
            "callable" => ["commController", "getPostCreateItem"],
        ],
        [
            "info" => "Comment a comment.",
            "requestMethod" => "get|post",
            "path" => "comment/{id:digit}",
            "callable" => ["commController", "getPostCommentItem"],
        ],
        [
            "info" => "Delete an item.",
            "requestMethod" => "get|post",
            "path" => "delete/{id:digit}",
            "callable" => ["commController", "getPostDeleteItem"],
        ],
        [
            "info" => "Update an item.",
            "requestMethod" => "get|post",
            "path" => "update/{id:digit}",
            "callable" => ["commController", "getPostUpdateItem"],
        ],
        [
            "info" => "See an item.",
            "requestMethod" => "get|post",
            "path" => "view-one/{id:digit}",
            "callable" => ["commController", "getPostShow"],
        ],
        [
            "info" => "Comment a comment.",
            "requestMethod" => "get|post",
            "path" => "commentpoints/{id:digit}",
            "callable" => ["commController", "getPostShowPoints"],
        ],
        [
            "info" => "See an item.",
            "requestMethod" => "get|post",
            "path" => "front",
            "callable" => ["commController", "getIndexPage"],
        ],
        [
            "info" => "See an item.",
            "requestMethod" => "get|post",
            "path" => "tags",
            "callable" => ["commController", "getTagList"],
        ],
        [
            "info" => "See an item.",
            "requestMethod" => "get|post",
            "path" => "tags/{id}",
            "callable" => ["commController", "getTagsShow"],
        ],
        [
            "info" => "See an item.",
            "requestMethod" => "get|post",
            "path" => "voteup/{id}",
            "callable" => ["commController", "makeVoteUp"],
        ],
        [
            "info" => "See an item.",
            "requestMethod" => "get|post",
            "path" => "votedown/{id}",
            "callable" => ["commController", "makeVoteDown"],
        ],
        [
            "info" => "See an item.",
            "requestMethod" => "get|post",
            "path" => "accept/{id}",
            "callable" => ["commController", "makeAccept"],
        ],
    ]
];
