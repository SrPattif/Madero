<?php
    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');

    $month = date('n');
    $year = date('Y');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moradias Grupo Madero | Iniciar preenchimento</title>

    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <div class="page-content">

        <img class="madero-logo" src="/assets/images/madero-white.png" alt="">

        <div class="header">
            <h2>Medições Finalizadas</h2>
            <p>Todas as medições foram realizadas. Não há mais moradias sem medições.</p>
        </div>

        <div class="home-btn">
            <a href="/moradias/reembolsos/">VOLTAR AO INÍCIO</a>
        </div>

    </div>

    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>

</body>

</html>