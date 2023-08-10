<?php
    if(!isset($_SESSION)) {
        session_start();
    }

    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

    $queryUsuarios = "SELECT * FROM usuarios;";
    $resultUsuarios = mysqli_query($mysqli, $queryUsuarios);
    $rowsUsuarios = array();
    while($row = mysqli_fetch_array($resultUsuarios)){
        array_push($rowsUsuarios, $row);
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
    <title>Controladoria Grupo Madero | Usuários e Credenciais</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="./defaultStyle.css" />
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
                    <h3>Usuários</h3>
                    <div class="option-list">
                        <div class="option" onclick="abrirModal('modal_cadastrarUsuario')">
                            <i class='bx bxs-user-plus'></i> CADASTRAR USUÁRIO
                        </div>
                    </div>
                </div>

                <input type="text" id="filterInput" placeholder="Digite o texto para filtrar">
                <table class="ranking-table" id="dataTable">
                    <tr>
                        <th>#</th>
                        <th>Usuário</th>
                        <th>Setor</th>
                        <th>Cargo</th>
                        <th>Último Login</th>
                        <th></th>
                    </tr>
                    <?php
                        foreach($rowsUsuarios as $usuario) {
                            $ultimoLogin = '-';
                            if(isset($usuario['last_login_at'])) {
                                $ultimoLogin = date_format(date_create($usuario['last_login_at']), "d/m/Y H:i");
                            }

                            $statusText = boolval($usuario['troca_senha']) ? "Troca de Senha Necessária" : "Ativo";
                            $statusColor = boolval($usuario['troca_senha']) ? "#bd6602" : "#048a01";

                            $statusText = boolval($usuario['bloqueado']) ? "Bloqueado" : $statusText;
                            $statusColor = boolval($usuario['bloqueado']) ? "#960008" : $statusColor;
                    ?>
                    <tr>
                        <td style="text-align: center;"><?php echo($usuario['id']); ?></td>
                        <td><?php echo($usuario['username']); ?></td>
                        <td style="text-align: center;"><?php echo($usuario['setor']); ?></td>
                        <td style="text-align: center;"><?php echo($usuario['cargo']); ?></td>
                        <td style="text-align: center;"><?php echo($ultimoLogin); ?></td>
                        <td style="text-align: center;"><span
                                style="font-weight: bold; color: <?php echo($statusColor); ?>;"><?php echo($statusText); ?></span>
                        </td>
                    </tr>
                    <?php
                        }
                    ?>
                </table>
            </div>

            <div id="modal_cadastrarUsuario" class="modal">
                <div class="modal-content vertical-center">
                    <div class="center">
                        <h2>Cadastrar Novo Usuário</h2>
                        <span>Insira abaixo as informações do novo usuário.</span>

                        <div class="double-inputs" style="width: 70%; margin: 1em auto;">
                            <div class="input-group" style="width: 100%;">
                                <label for="input_nome">Nome e Sobrenome</label>
                                <input type="text" id="input_nome" value="">
                            </div>
                        </div>
                        <div class="double-inputs" style="width: 70%; margin: 1em auto;">
                            <div class="input-group" style="width: 50%;">
                                <label for="input_username">Nome de Usuário</label>
                                <input type="text" id="input_username" value="" oninput="checkAndAutoFillEmail()">
                            </div>
                            <div class="input-group" style="width: 50%;">
                                <label for="input_email">E-mail</label>
                                <input type="text" id="input_email" value="">
                            </div>
                        </div>
                        <div class="double-inputs" style="width: 70%; margin: 1em auto;">
                            <div class="input-group" style="width: 50%;">
                                <label for="input_setor">Setor</label>
                                <input type="text" id="input_setor" value="Controladoria">
                            </div>
                            <div class="input-group" style="width: 50%;">
                                <label for="input_cargo">Cargo</label>
                                <input type="text" id="input_cargo" value="">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="double-buttons">
                            <div class="simple-button" onclick="closeModal('modal_cadastrarUsuario')"
                                style="width: 50%;">
                                FECHAR</div>
                            <div class="button" id="btn_cadastrarUsuario" style="width: 50%;">
                                CADASTRAR USUÁRIO</div>
                        </div>
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
    function abrirModal(modalId) {
        $("#" + modalId).css("display", "flex");

        setTimeout(function() {
            $("#" + modalId).addClass("show");
        }, 10);
    }

    function closeModal(modalId) {
        $("#" + modalId).removeClass("show");

        setTimeout(function() {
            $("#" + modalId).css("display", "none");
        }, 300);
    }

    $(document).ready(function() {
        $(document).on("keyup", function(event) {
            if (event.keyCode === 27) {
                $('[id^="modal_"]').removeClass("show");

                setTimeout(function() {
                    $('[id^="modal_"]').css("display", "none");
                }, 300);
            }
        });
    });

    const filterInput = document.getElementById('filterInput');
    const dataTable = document.getElementById('dataTable');

    filterInput.addEventListener('input', function() {
        filterTable();
    });

    function filterTable() {
        const filterText = filterInput.value.toLowerCase();
        const rows = dataTable.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const cells = row.getElementsByTagName('td');
            let found = false;

            for (let j = 0; j < cells.length; j++) {
                const cell = cells[j];
                const cellText = cell.textContent.toLowerCase();

                if (cellText.includes(filterText)) {
                    found = true;
                    break;
                }
            }

            if (found) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    function checkAndAutoFillEmail() {
        const inputUsername = document.getElementById('input_username');
        const inputEmail = document.getElementById('input_email');

        // Obtém o valor do input_username e remove espaços em branco no início e fim
        const usernameValue = inputUsername.value.trim();

        // Se o input_email estiver vazio e o input_username não estiver vazio
        inputEmail.value = usernameValue + '@grupomadero.com.br';
    }

    $('#btn_cadastrarUsuario').click(() => {
        var usuario = $('#input_username').val();
        var nome = $('#input_nome').val();
        var email = $('#input_email').val();
        var setor = $('#input_setor').val();
        var cargo = $('#input_cargo').val();

        if (email == '' || nome  ==  '' || usuario == '' || setor == '' || cargo == '') {
            tata.error('Preencha os campos obrigatórios',
                'É necessário preencher todos os campos obrigatórios para cadastrar um novo usuário.', {
                    duration: 6000
                });
            return;
        }

        closeModal('modal_cadastrarUsuario');

        $('#input_username').val("");
        $('#input_nome').val("");
        $('#input_email').val("");
        $('#input_setor').val("");
        $('#input_cargo').val("");

        $.ajax({
            url: '/ferramentas/usuarios/backend/cadastrarUsuario.php',
            type: 'POST',
            data: {
                usuario: usuario,
                email: email,
                nome: nome,
                setor: setor,
                cargo: cargo
            },
            success: function(response) {
                console.log(response);
                tata.success('Usuário cadastrato',
                    'O usuario foi cadastrado com sucesso.', {
                        duration: 3000
                    });

                setTimeout(() => {
                    window.location.reload();
                }, 2500);
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao cadastrar o usuario. (' +
                    xhr.responseText + ')', {
                        duration: 6000
                    });
            }
        });
    });
    </script>

</body>

</html>