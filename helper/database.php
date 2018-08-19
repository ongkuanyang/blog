<?php
define("DBHOST", "localhost");
define("DBNAME", "blog");
define("DBUSER", "blogserver");
define("DBPASS", "password");

function db_connect() {
    $conn = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    if ($conn->connect_error) die($conn->connect_error);
    return $conn;
}

// It is recommended to use prepared statements instead of escaping strings.

function sanitizeString($conn, $string) {
    /* $string = strip_tags($string);
     * $string = htmlentities($string);
     * $string = stripslashes($string);*/
    return $conn->real_escape_string($string);
}
?>
