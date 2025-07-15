<?php
    function controlla_email(&$email) : bool {
        if (isset($_POST["email"])) {
            $email = $_POST["email"];
            $regex_email = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
            if (!preg_match($regex_email, $email)) {
                echo "Email non nel formato corretto: $email";
                return false;
            }
            return true;
        }
        return false;
    }

    function controlla_password(&$password) : bool {
        if (isset($_POST["password"])) {
            $password = $_POST["password"];
            $regexPass = "/^[A-Z][A-Za-z0-9'\$+@]{4,16}$/";
            if (!preg_match($regexPass, $password)) {
                echo "Password non nel formato corretto";
                return false;
            }
            return true;
        }
        return false;
    }