<?php
    if(!isset($_SESSION)) {
        session_start();
    }

    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

    $idUsuario = $_GET['idUsuario'];
    
    if (!is_numeric($idUsuario)) {
        header('location: ../');
        exit();
    }

    $idUsuario = mysqli_real_escape_string($mysqli, $idUsuario);

    $descricaoEventos = array();
    $descricaoEventos['conta.criar'] = "<i class='bx bxs-user-plus' ></i> Criação da Conta";
    $descricaoEventos['conta.autenticar'] = "<i class='bx bxs-door-open'></i> Autenticação";
    $descricaoEventos['conta.trocarSenha'] = "<i class='bx bxs-lock' ></i> Mudança de Senha";

    $queryUsuario = "SELECT * FROM usuarios WHERE id={$idUsuario};";
    $resultUsuario = mysqli_query($mysqli, $queryUsuario);
    if(mysqli_num_rows($resultUsuario) != 1) {
        header('location: ../');
        exit();
    }

    $queryModulos = "SELECT * FROM modulos WHERE ativo=1 ORDER BY categoria;";
    $resultModulos = mysqli_query($mysqli, $queryModulos);
    $rowsModulos = array();
    while ($row = mysqli_fetch_array($resultModulos)) {
        array_push($rowsModulos, $row);
    }

    $queryHistorico = "SELECT * FROM historico_contas WHERE id_usuario={$idUsuario} ORDER BY data DESC LIMIT 5;";
    $resultHistorico = mysqli_query($mysqli, $queryHistorico);
    $rowsHistorico = array();
    while ($row = mysqli_fetch_array($resultHistorico)) {
        array_push($rowsHistorico, $row);
    }

    $modulosPermitidos = array();

    $queryPermissoes = "SELECT * FROM permissoes WHERE id_usuario={$idUsuario};";
    $resultPermissoes = mysqli_query($mysqli, $queryPermissoes);
    $rowsPermissoes = array();
    while ($row = mysqli_fetch_array($resultPermissoes)) {
        array_push($rowsPermissoes, $row);
    }

    foreach ($rowsPermissoes as $moduloPermitido) {
        array_push($modulosPermitidos, (int) $moduloPermitido['id_modulo']);
    }

    $usuario = mysqli_fetch_assoc($resultUsuario);

    $acessoTotal = $usuario['nivel_acesso'] == "TOTAL" ? 'true' : 'false';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controladoria Grupo Madero | Detalhes do Usuário</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="../defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/ferramentas/usuarios/header.php');
    ?>

    <main>
        <div class="page-content">
            <div class="card">
                <div class="card-header">
                    <h3><?php echo($usuario['nome']); ?></h3>
                </div>
                <div class="double-inputs">
                    <div class="input-group" style="width: 10%;">
                        <label for="input_id">Identificador</label>
                        <input type="text" id="input_id" value="<?php echo((int) $usuario['id']); ?>"
                            style="text-align: center;" readonly>
                    </div>
                    <div class="input-group" style="width: 45%;">
                        <label for="input_id">Nome Completo</label>
                        <input type="text" id="input_id" value="<?php echo($usuario['nome']); ?>">
                    </div>
                    <div class="input-group" style="width: 45%;">
                        <label for="input_email">E-mail</label>
                        <input type="text" id="input_email" value="<?php echo($usuario['email']); ?>">
                    </div>
                </div>
                <div class="double-inputs">
                    <div class="input-group" style="width: 30%;">
                        <label for="input_usuario">Usuário</label>
                        <input type="text" id="input_usuario" value="<?php echo($usuario['username']); ?>" readonly>
                    </div>
                    <div class="input-group" style="width: 35%;">
                        <label for="input_usuario">Setor</label>
                        <input type="text" id="input_usuario" value="<?php echo($usuario['setor']); ?>">
                    </div>
                    <div class="input-group" style="width: 35%;">
                        <label for="input_email">Cargo</label>
                        <input type="text" id="input_email" value="<?php echo($usuario['cargo']); ?>">
                    </div>
                </div>

                <button class="button" id="btn_salvarAlojamento" style="margin-bottom: 2em;">SALVAR</button>

                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Permissões do Usuário</h3>
                    </div>
                    <div class="access-selector">
                        <div class="access" id="button_parcialAccess"><i class='bx bxs-user-badge'></i> Aceso Parcial
                        </div>
                        <div class="access" id="button_fullAccess"><i class='bx bxs-crown'></i> Acesso Total</div>
                    </div>
                    <div class="card" id="card_permissions">
                        <?php
                            foreach ($rowsModulos as $moduloObj) {
                                $idModulo = $moduloObj['id'];
                                $categoriaModulo = $moduloObj['categoria'];
                                $nomeModulo = $moduloObj['modulo'];
                                $iconeModulo = $moduloObj['icone'];

                                $strModulo = "<i class='bx " . $iconeModulo . "' ></i> " . $categoriaModulo . " / <span class='module-name'>" . $nomeModulo . "</span>";
                        ?>
                        <div class="modules-list">
                            <div class="module">
                                <div class="control-group">
                                    <label class="control control-checkbox">
                                        <?php echo($strModulo); ?>
                                        <input type="checkbox" <?php echo(in_array($idModulo, $modulosPermitidos) ? 'checked' : ''); ?> id="modulo_<?php echo($idModulo); ?>" />
                                        <div class="control_indicator"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php
                            }
                        ?>
                    </div>

                    <button class="button" id="btn_salvarPermissoes" style="margin-bottom: 2em;">SALVAR</button>
                </div>

                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Atividade do Usuário</h3>
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
                                <p class="timeline-event-description"><?php echo($dataEvento); ?><br>IP:
                                    <?php echo($evento['endereco_ip']) ?></p>
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

    <?php
    //require('footer.php');
    ?>

    <script src="/mobile-navbar.js"></script>
    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
    $(document).ready(() => {
        var acessoTotal = <?php echo($acessoTotal) ?>;

        if (acessoTotal) {
            $('#button_fullAccess').addClass("access-selected");
            $('#button_parcialAccess').removeClass("access-selected");

            $('#card_permissions').hide();
        } else {
            $('#button_fullAccess').removeClass("access-selected");
            $('#button_parcialAccess').addClass("access-selected");

            $('#card_permissions').show();
        }
    })
    $('#button_fullAccess').click(() => {
        $('#button_fullAccess').addClass("access-selected");
        $('#button_parcialAccess').removeClass("access-selected");

        $('#card_permissions').hide();
    })

    $('#button_parcialAccess').click(() => {
        $('#button_fullAccess').removeClass("access-selected");
        $('#button_parcialAccess').addClass("access-selected");

        $('#card_permissions').show();
    })

    $('#btn_salvarPermissoes').click(() => {
        var userModules = [];
        var userId = $('#input_id').val();

        $('[id^="modulo_"]').each(function() {
            var moduleId = this.id.split('_').pop();
            var checked = this.checked;

            if(checked) {
                userModules.push(moduleId);
            }
        });

        console.log(userId)
        console.log(userModules);

        $.ajax({
            type: "POST",
            url: "/ferramentas/usuarios/backend/atualizarPermissoes.php",
            data: {
                idUsuario: userId,
                modulos: userModules,
            },
            success: function(result) {
                tata.success('Permissões atualizadas',
                    'As permissões do usuário foram atualizadas com sucesso.', {
                        duration: 3000
                    });

                setTimeout(() => {
                    window.location.reload();
                }, 2500);
                return;
            },
            error: function(result) {
                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao tentar atualizar as permissões do usuário. (' + result
                    .responseJSON.mensagem + ')', {
                        duration: 6000
                    });
            }
        });

    })
    </script>

</body>

</html>