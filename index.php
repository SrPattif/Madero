<?php
if (!isset($_SESSION)) {
    session_start();
}

include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

$nomeUsuario = $_SESSION['USER_NAME'];
$nomeUsuario = explode(" ", $nomeUsuario)[0];

$queryModulos = "SELECT * FROM modulos WHERE ativo=1 ORDER BY `status`='ok' DESC;";
$resultModulos = mysqli_query($mysqli, $queryModulos);
$rowsModulos = array();
while ($row = mysqli_fetch_array($resultModulos)) {
    array_push($rowsModulos, $row);
}

$modulos = array();
$modulosPath = array();
$modulosNomes = array();
$modulosDescricoes = array();
$modulosCategorias = array();
$modulosIcones = array();
$modulosStatus = array();
foreach($rowsModulos as $modulo) {
    $idModulo = $modulo['id'];
    $nomeModulo = $modulo['modulo'];
    $categoriaModulo = $modulo['categoria'];
    $caminhoModulo = $modulo['caminho'];
    $iconeModulo = $modulo['icone'];
    $descricaoModulo = $modulo['descricao'];
    $statusModulo = $modulo['status'];

    if(!isset($modulos[$categoriaModulo])) {
        $modulos[$categoriaModulo] = array();
    }

    if(!in_array($categoriaModulo, $modulosCategorias)) {
        array_push($modulosCategorias, $categoriaModulo);
    }

    array_push($modulos[$categoriaModulo], $idModulo);
    $modulosPath[$idModulo] = $caminhoModulo;
    $modulosNomes[$idModulo] = $nomeModulo;
    $modulosIcones[$idModulo] = $iconeModulo;
    $modulosDescricoes[$idModulo] = $descricaoModulo;
    $modulosStatus[$idModulo] = $statusModulo;
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
    <title>Controladoria | Grupo Madero</title>

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
            <div class="page-title">
                <h2>Olá, <?php echo($nomeUsuario); ?>! 👋</h2>

                <div class="card card-modulo" onclick="window.location.href='/conta/'">
                    <h4><i class='bx bxs-user'></i> Sua Conta</h4>
                    <span class="module-description">Veja seus dados e altere sua senha.</span>

                    <i class='icon bx bx-chevron-right chevron'></i>
                </div>

                <br>

                <h3>Selecione um módulo</h3>
                <span style="font-size: 1rem;">Selecione um módulo do sistema para prosseguir.</span>
            </div>
            <?php
                foreach($modulosCategorias as $categoria) {
            ?>

            <div class="card">
                <div class="module-category"><?php echo($categoria); ?></div>

                <?php
                    foreach ($modulos[$categoria] as $id) {
                        $status = $modulosStatus[$id];

                        $statusClass = '';
                        $statusText = '';
                        $indicatorIcon = "<i class='icon bx bx-chevron-right chevron'></i>";

                        switch ($status) {
                            case 'em_breve':
                                $statusClass = 'soon';
                                $statusText = 'EM BREVE';
                                $indicatorIcon = "<i class='icon bx bx-time-five'></i>";
                                break;

                            case 'testes':
                                $statusClass = 'testing';
                                $statusText = 'EM TESTES';
                                break;
                            
                            default:
                                # code...
                                break;
                        }
                ?>

                <div class="card card-modulo" onclick="window.location.href='<?php echo($modulosPath[$id]); ?>'">
                <?php
                    if($status != "ok") {
                ?>
                    <div class="<?php echo($statusClass); ?>"><?php echo($statusText); ?></div>
                <?php
                    }
                ?>
                    <h5><i class='bx <?php echo($modulosIcones[$id]); ?>'></i> <?php echo($modulosNomes[$id]); ?></h5>
                    <span class="module-description"><?php echo($modulosDescricoes[$id]); ?></span>

                    <?php echo($indicatorIcon); ?>
                </div>

                <?php
                    }
                ?>
            </div>

            <?php
                }
            ?>
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