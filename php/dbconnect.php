<?php
    require_once "dbutility.php";
    $connessione = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
    if(mysqli_connect_errno()) {
        die(mysqli_connect_error());
        echo mysqli_connect_error();
    }
?>