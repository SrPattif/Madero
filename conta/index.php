<?php
if (!isset($_SESSION)) {
    session_start();
}

include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');
include($_SERVER['DOCUMENT_ROOT'] . '/libs/utils.php');
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

$userId = mysqli_real_escape_string($mysqli, $_SESSION['USER_ID']);

$userData = getUserData($userId);

if($userData == null) {
    header('location: /');
    exit();
}

$descricaoEventos = array();
$descricaoEventos['conta.criar'] = "<i class='bx bxs-user-plus' ></i> Criação da Conta";
$descricaoEventos['conta.autenticar'] = "<i class='bx bxs-door-open'></i> Autenticação";
$descricaoEventos['conta.trocarSenha'] = "<i class='bx bxs-lock' ></i> Mudança de Senha";

$nomeUsuario = $userData['nome'];
$primeiroNome = explode(" ", $nomeUsuario)[0];
$setor = $userData['setor'];
$cargo = $userData['cargo'];
$username = $userData['username'];
$email = $userData['email'];

$queryHistorico = "SELECT * FROM historico_contas WHERE id_usuario=$userId ORDER BY data DESC LIMIT 5;";
$resultHistorico = mysqli_query($mysqli, $queryHistorico);
$rowsHistorico = array();
while ($row = mysqli_fetch_array($resultHistorico)) {
    array_push($rowsHistorico, $row);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2F2Z7S0VR0"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    </script>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua Conta | Controladoria Grupo Madero</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="./defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/header.php');
    ?>

    <main>
        <div class="page-content">
            <div class="card">
                <h2><?php echo($nomeUsuario); ?></h2>
                <div class="user-description bold"><?php echo($username); ?></div>
                <div class="user-description"><?php echo($cargo); ?>, <?php echo($setor); ?>.</div>
                <div class="user-description"><?php echo($email); ?></div>

                <br>
                
                <div class="simple-button" style="color: #2f2f94;"><i class='bx bxs-lock-alt'></i> MUDAR SENHA</div>
                <div class="simple-button" style="color: #AA0000;"><i class='bx bx-log-out'></i> DESCONECTAR</div>

                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Suas Permissões</h3>
                    </div>
                    <span style="font-size: 1rem;">Você possui <span class="bold">acesso total</span> ao sistema.</span>
                </div>

                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Sua Atividade</h3>
                    </div>
                    <div class="timeline">
                        <?php
                            foreach($rowsHistorico as $evento) {
                                $tipoEvento = $evento['tipo_evento'];
                                $dataEvento = date_format(date_timezone_set(date_create($evento['data']), timezone_open('America/Sao_Paulo')), "d/m/Y H:i:s");
                        ?>
                        <div class="timeline-event">
                            <div class="timeline-event-icon"></div>
                            <div class="timeline-event-content">
                                <p class="timeline-event-date"><?php echo($descricaoEventos[$tipoEvento]); ?></p>
                                <p class="timeline-event-description"><?php echo($dataEvento); ?></p>
                            </div>
                        </div>
                        <?php
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="mobile-navbar.js"></script>
    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    </script>

</body>

</html>