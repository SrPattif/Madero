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
$imagemPerfil = "/assets/images/default-user.png";
if(isset($userData['imagem_perfil'])) {
    $imagemPerfil = "/uploads/pfp/" . $userData['imagem_perfil'];
}

$queryHistorico = "SELECT * FROM historico_contas WHERE id_usuario=$userId ORDER BY data DESC LIMIT 5;";
$resultHistorico = mysqli_query($mysqli, $queryHistorico);
$rowsHistorico = array();
while ($row = mysqli_fetch_array($resultHistorico)) {
    array_push($rowsHistorico, $row);
}
?>

<!DOCTYPE html>
<html lang="pt_BR">

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
            <div class="cards-container">
                <div class="left">
                    <div class="card">
                        <div class="user-details">
                            <div class="user-image" style="background-image: url('<?php echo($imagemPerfil); ?>');">
                            </div>
                            <h2><?php echo($nomeUsuario); ?></h2>
                            <div class="user-description bold"><?php echo($username); ?></div>
                            <div class="user-description"><?php echo($cargo); ?>, <?php echo($setor); ?>.</div>
                            <div class="user-description"><?php echo($email); ?></div>
                        </div>
                    </div>
                    <div class="card card-button" onclick="abrirModal('modal_foto')">
                        ALTERAR FOTO
                        <i class='bx bx-right-arrow-alt'></i>
                    </div>
                    <div class="card card-button">
                        MUDAR SENHA
                        <i class='bx bx-right-arrow-alt'></i>
                    </div>
                    <a href="/login/desconectar.php">
                        <div class="card card-button disconnect">
                            DESCONECTAR
                            <i class='bx bx-log-out'></i>
                        </div>
                    </a>
                </div>

                <div class="right">
                    <div class="card">
                        <div class="card-header">
                            <h3>Suas Permissões</h3>
                        </div>
                        <span style="font-size: 1rem;">Você possui <span class="bold">acesso total</span> ao
                            sistema.</span>
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
        </div>

        <div id="modal_foto" class="modal">
            <div class="modal-content">
                <div class="drop-files" id="drop-files-area">
                    <form class="center">
                        <span><i class='bx bx-cloud-upload'></i></span>
                        <h4>Solte aqui sua nova foto de perfil</h4>
                        <p id="drop-description" class="font-size: .2em;">Faça uma boa escolha!</p>
                    </form>
                </div>

                <div class="button" onclick="closeModal('modal_foto')" style="width: 100%;">FECHAR</div>
            </div>
        </div>
    </main>

    <script src="mobile-navbar.js"></script>
    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    //


    let dropArea = document.getElementById('drop-files-area')
    let dropInstruictions = document.getElementById('drop-description');
    let filesDone = 0
    let filesToDo = 0
    let progressBar = document.getElementById('progress-bar')


    ;
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false)
    })

    function preventDefaults(e) {
        e.preventDefault()
        e.stopPropagation()
    }

    ;
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false)
    })

    ;
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false)
    })

    function highlight(e) {
        dropArea.classList.add('highlight')
        dropInstruictions.style = 'display: none;';

    }

    function unhighlight(e) {
        dropArea.classList.remove('highlight')
        dropInstruictions.style = 'display: block;';
    }

    dropArea.addEventListener('drop', handleDrop, false)

    function handleDrop(e) {
        closeModal('modal_foto');

        let dt = e.dataTransfer;
        let files = dt.files;

        if (files.length == 1) {
            let file = files[0];

            if (file.type == "image/png" || file.type == "image/jpeg") {
                uploadFile(file);

            } else {
                tata.error('Tipo de Arquivo Inválido', 'Apenas imagens podem ser enviadas.', {
                    duration: 3000
                });
            }
        } else {
            tata.error('Quantidade de Arquivos Inválida', 'Você pode enviar apenas uma imagem.', {
                duration: 3000
            });
        }
    }

    function uploadFile(file) {
        let formData = new FormData()

        formData.append('file', file)

        $.ajax({
            url: 'uploadPFP.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log(response);
                if (!response.salvo) {
                    tata.error('Erro ao processar arquivo', 'Ocorreu um erro ao processar o arquivo.', {
                        duration: 3000
                    })
                } else {
                    tata.success('Imagem de Perfil Alterada',
                        'Sua imagem de perfil foi alterada com sucesso!', {
                            duration: 3000
                        })
                }

                setTimeout(() => {
                    window.location.reload();
                }, 2500);
            },
            error: function(response) {
                console.log(response);
                
                tata.error('Erro ao processar arquivo', 'Ocorreu um erro ao processar o arquivo.', {
                    duration: 3000
                })
            }
        });
    }
    </script>

</body>

</html>