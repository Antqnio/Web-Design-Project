<?php
    function controlla_password(string $password_key) : void {
        $password = $_POST[$password_key];
        $regex_password = "/^[A-Z][A-Za-z0-9'\$+@]{4,16}$/";
        if (!preg_match($regex_password, $password)) {
            echo "$password_key nel formato non corretto";
            exit();
        }
    }
?>