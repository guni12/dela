<?php
/**
 * Config file for Database.
 * Change localhost to the server that you are going to use
 */

$list = [
    "dsn"             => "mysql:host=localhost;port=xxxx;dbname=xxxx;",
    "username"        => "xxxx",
    "password"        => "xxxx",
    "driver_options"  => [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"],
    "fetch_mode"      => \PDO::FETCH_OBJ,
    "table_prefix"    => null,
    "session_key"     => "xxxx",
    // True to be very verbose during development
    "verbose"         => null,

    // True to be verbose on connection failed
    "debug_connect"   => false,
];


return $list;
