<?php
    $HOSTNAME = "localhost";
    $USERNAME = "root";
    $PASSWORD = "";
    $DATABASE = "ict";

    $con = new mysqli($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);

    if ($con->connect_error) {
        die('Failed to connect to database: ' . $con->connect_error);
    }
?>