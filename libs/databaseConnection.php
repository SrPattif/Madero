<?php
    require($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

    $dotenv = Dotenv\Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
    $dotenv->load();

    $mysqli = new mysqli($_ENV['DATABASE_HOST'], $_ENV['DATABASE_USERNAME'], $_ENV['DATABASE_PASSWORD']);
    if($mysqli->connect_errno) {
        echo('Ocorreu um erro ao tentar se conectar aos serviços.');
    }
    mysqli_select_db($mysqli, $_ENV['DATABASE_NAME']);
?>