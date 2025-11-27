<?php
    $serverName = "localhost";
    $userName = "root";
    $password = "";
    $dataBase = "EduMind";

    $pdo = new PDO("mysql:host=$serverName;dbname=$dataBase", $userName, $password);
?>