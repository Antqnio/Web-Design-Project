<?php
    session_start();
    session_unset();
    header("location: index.php");
    exit(); //Assicura che questo script venga terminato dopo il redirect.
?>