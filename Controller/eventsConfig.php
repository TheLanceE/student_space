<?php
    $serverName = "localhost";
    $userName = "root";
    $password = "";
    $dataBase = "Events";

    $pdo = new PDO("mysql:host=$serverName;dbname=$dataBase", $userName, $password);
?>