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
            <h2>Iniciar Preenchimento</h2>
            <p>Para iniciar o preenchimento, selecione o ano e mês da base de dados.</p>
        </div>

        <div class="date-selectors">
            <div class="select">
                <select id="select-year">
                    <option value="reject">Ano</option>
                    <option value="2023" <?php if($year == 2023) echo('selected'); ?>>2023</option>
                    <option value="2024" <?php if($year == 2024) echo('selected'); ?>>2024</option>
                </select>
                <div class="select_arrow">
                </div>
            </div>

            <div class="select">
                <select id="select-month">
                    <option value="reject">Mês</option>
                    <option value="1" <?php if($month == 1) echo('selected'); ?>>Janeiro</option>
                    <option value="2" <?php if($month == 2) echo('selected'); ?>>Fevereiro</option>
                    <option value="3" <?php if($month == 3) echo('selected'); ?>>Março</option>
                    <option value="4" <?php if($month == 4) echo('selected'); ?>>Abril</option>
                    <option value="5" <?php if($month == 5) echo('selected'); ?>>Maio</option>
                    <option value="6" <?php if($month == 6) echo('selected'); ?>>Junho</option>
                    <option value="7" <?php if($month == 7) echo('selected'); ?>>Julho</option>
                    <option value="8" <?php if($month == 8) echo('selected'); ?>>Agosto</option>
                    <option value="9" <?php if($month == 9) echo('selected'); ?>>Setembro</option>
                    <option value="10" <?php if($month == 10) echo('selected'); ?>>Outubro</option>
                    <option value="11" <?php if($month == 11) echo('selected'); ?>>Novembro</option>
                    <option value="12" <?php if($month == 12) echo('selected'); ?>>Dezembro</option>
                </select>
                <div class="select_arrow">
                </div>
            </div>
        </div>

        <div id="continue-btn" class="continue-btn">
            CONTINUAR <i class='bx bx-right-arrow-alt'></i>
        </div>

        <div class="home-btn">
            <a href="/">VOLTAR AO INÍCIO</a>
        </div>

    </div>

    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
        $('#continue-btn').on('click', () => {
            let year = $( "#select-year").val();
            let month = $( "#select-month" ).val();

            window.location.href = `../medir/?year=${year}&month=${month}`;
        })
    </script>

</body>

</html>