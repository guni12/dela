<?php
/**
 * Config-file for sessions.
 * ANAX_APP_PATH or __DIR__
 */

return [
    "name" => preg_replace("/[^a-z\d]/i", "", ANAX_APP_PATH),
];
