<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

$operacaoQuery = "";
if(isset($_GET['operacao'])) {
    $operacaoQuery = mysqli_real_escape_string($mysqli, $_GET['operacao']);
}

$queryOperacoes = "SELECT DISTINCT operacao, filial FROM ativos ORDER BY filial ASC;"; // Obter todas as operações disponíveis.
$resultOperacoes = mysqli_query($mysqli, $queryOperacoes);
$rowsOperacoes = array();
while($row = mysqli_fetch_array($resultOperacoes)){
    array_push($rowsOperacoes, $row);
}

$rowsAlojamentos = array();
$rowsSindicos = array();
if(!empty($operacaoQuery)) {
    $queryAlojamentos = "SELECT id,digito_financeiro, operacao, endereco FROM alojamentos ORDER BY operacao ASC;"; // Obter todas as moradias disponíveis.
    $resultAlojamentos = mysqli_query($mysqli, $queryAlojamentos);
    while($row = mysqli_fetch_array($resultAlojamentos)){
        array_push($rowsAlojamentos, $row);
    }
    
    $querySindicos = "SELECT chapa AS id, operacao, nome, funcao FROM ativos WHERE operacao='{$operacaoQuery}' ORDER BY operacao ASC;"; // Obter todos os colaboradores disponíveis.
    $resultSindicos = mysqli_query($mysqli, $querySindicos);
    while($row = mysqli_fetch_array($resultSindicos)){
        array_push($rowsSindicos, $row);
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Troca de Síndico | Controladoria Grupo Madero</title>

    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>

    <div class="page-content">
        <div class="container">
            <div class="step" id="step-1">
                <div class="header">
                    <span>TROCA DE SÍNDICO</span>
                    <h2>Número de Telefone para Contato</h2>
                    <p>Entraremos em contato com o síndico da moradia.</p>
                </div>

                <div class="input-data">
                    <input type="text" id="input_telefone" placeholder="(00) 90000-0000">
                </div>
            </div>

            <div class="step" id="step-2">
                <div class="header">
                    <span>TROCA DE SÍNDICO</span>
                    <h2>Operação da Moradia</h2>
                    <p>Selecione a operação do novo síndico da moradia.</p>
                </div>

                <div class="input-data">
                    <div id="btn_selecionarOperacao" class="btn-selec">
                        SELECIONAR OPERAÇÃO
                    </div>
                </div>
            </div>

            <div class="step" id="step-3">
                <div class="header">
                    <span>TROCA DE SÍNDICO</span>
                    <h2>Selecione o endereço da moradia</h2>
                    <p>A moradia que está recebendo um novo síndico.</p>
                </div>

                <div class="input-data">
                    <div id="btn_selecionarMoradia" class="btn-selec">
                        SELECIONAR MORADIA
                    </div>
                </div>
            </div>

            <div class="step" id="step-4">
                <div class="header">
                    <span>TROCA DE SÍNDICO</span>
                    <h2>Novo Síndico da Moradia</h2>
                    <p>Selecione o colaborador que é o novo síndico da moradia.</p>
                </div>

                <div class="input-data">
                    <div id="btn_selecionarSindico" class="btn-selec">
                        SELECIONAR SÍNDICO
                    </div>
                </div>
            </div>

            <div class="continue-btn" id="btn_continuar">
                PRÓXIMO <i class='bx bx-right-arrow-alt'></i>
            </div>
            <div class="home-btn">
                <a href="/formularios/sindicos/">VOLTAR AO INÍCIO</a>
            </div>
        </div>

        <div id="modal_operacoes" class="modal">
            <div class="modal-content">
                <h2>Selecionar Operação</h2>
                <span>Selecione a operação desejada.</span>
                <div class="filter">
                    <input type="text" id="input_operacaoFiltro" oninput="filtrarModal('operacao')"
                        placeholder="Digite a operação para pesquisar">
                </div>
                <div class="painel" id="painel-operacao">
                    <ul>
                        <?php
                            foreach($rowsOperacoes as $row) {
                                $operacao = $row['operacao'];
                                $filial = $row['filial'];

                                $tipoOperacao = "";
                                $displayOperacao = "";

                                switch (substr($operacao, 0, 2)) {
                                    case 'MD':
                                        $tipoOperacao = "MD";
                                        $displayOperacao = "MADERO";
                                        break;

                                    case 'JE':
                                        $tipoOperacao = "JE";
                                        $displayOperacao = "JERONIMO";
                                        break;

                                    case 'PM':
                                        $tipoOperacao = "PM";
                                        $displayOperacao = "ECOPARADA MADERO";
                                        break;
                                    
                                    default:
                                        # code...
                                        break;
                                }

                        ?>
                        <li onclick="selecionarOperacao('<?php echo($operacao); ?>')">
                            <div class="desc">
                                <span class="badge badge-gray"><?php echo($filial); ?></span>
                                <span
                                    class="badge badge-<?php echo($tipoOperacao); ?>"><?php echo($displayOperacao); ?></span>
                            </div>
                            <span class="nome" id="span_operacaoNome"><?php echo($operacao); ?></span>
                        </li>
                        <?php
                            }
                        ?>
                    </ul>
                </div>
                <div class="button" onclick="closeModal('modal_operacoes')" style="width: 100%;">FECHAR</div>
            </div>
        </div>

        <div id="modal_moradias" class="modal">
            <div class="modal-content">
                <h2>Selecionar Moradia</h2>
                <span>Selecione a moradia desejada.</span>
                <div class="filter">
                    <input type="text" id="input_moradiaFiltro" oninput="filtrarModal('moradia')"
                        placeholder="Digite o endereço para pesquisar">
                </div>
                <div class="painel" id="painel-moradia">
                    <ul>
                        <?php
                            foreach($rowsAlojamentos as $row) {
                                $idAlojamento = $row['id'];
                                $digito = $row['digito_financeiro'];
                                $operacao = $row['operacao'];
                                $endereco = $row['endereco'];
                        ?>
                        <li onclick="selecionarMoradia('<?php echo($idAlojamento); ?>', '<?php echo($endereco); ?>')">
                            <div class="desc">
                                <span><?php echo($operacao); ?></span>
                            </div>
                            <span class="nome" id="span_moradiaNome"><?php echo($endereco); ?></span>
                        </li>
                        <?php
                            }
                        ?>
                    </ul>
                </div>
                <div class="button" onclick="closeModal('modal_moradias')" style="width: 100%;">FECHAR</div>
            </div>
        </div>

        <div id="modal_sindicos" class="modal">
            <div class="modal-content">
                <h2>Selecionar Colaborador</h2>
                <span>Selecione o colaborador que é síndico da moradia.</span>
                <a href="#" class="bold">Não consegue encontrá-lo na lista?</a>
                <div class="filter">
                    <input type="text" id="input_sindicoFiltro" oninput="filtrarModal('sindico')"
                        placeholder="Digite o nome para pesquisar">
                </div>
                <div class="painel" id="painel-sindico">
                    <ul>
                        <?php
                            foreach($rowsSindicos as $row) {
                                $idSindico = $row['id'];
                                $nome = $row['nome'];
                                $operacao = $row['operacao'];
                                $funcao = $row['funcao'];
                        ?>
                        <li onclick="selecionarSindico('<?php echo($idSindico); ?>', '<?php echo($nome); ?>')">
                            <div class="desc">
                                <span><?php echo($funcao); ?></span>
                            </div>
                            <span class="nome" id="span_sindicoNome"><?php echo($nome); ?></span>
                        </li>
                        <?php
                            }
                        ?>
                    </ul>
                </div>
                <div class="button" onclick="closeModal('modal_sindicos')" style="width: 100%;">FECHAR</div>
            </div>
        </div>


    </div>



    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
    <script>
    var data = {};
    var step = 1;

    if (localStorage.getItem("form_step") && typeof(parseInt(localStorage.getItem("form_step"))) == "number" &&
        localStorage.getItem("form_step") > 0 && localStorage.getItem("form_step") <= 5) {
        step = localStorage.getItem("form_step");
    }

    $('#btn_continuar').on('click', () => {
        if (step == 1) {
            var telefone = $('#input_telefone').val();
            if (telefone && telefone.length == 15) {
                localStorage.setItem("form_telephone", telefone)
                localStorage.setItem("form_step", 2)

                window.location.reload();

            } else {
                tata.error('Telefone inválido', 'O número de telefone informado é inválido.', {
                    duration: 6000
                });
                return;
            }

        } else if (step == 2) {
            if (data.operacao && data.operacao.length > 0) {
                var operacao = data.operacao;
                localStorage.setItem("form_shop", operacao)
                localStorage.setItem("form_step", 3)

                window.location.href = "./?operacao=" + operacao;

            } else {
                tata.error('Operação inválida', 'A operação selecionada é inválida.', {
                    duration: 6000
                });
                return;
            }

        } else if (step == 3) {
            if (data.alojamento && typeof(parseInt(data.alojamento)) == "number" && data.alojamento > 0) {
                var alojamento = data.alojamento;
                localStorage.setItem("form_accommodation", alojamento)
                localStorage.setItem("form_step", 4)

                window.location.reload();

            } else {
                tata.error('Moradia inválida', 'A moradia selecionada é inválida.', {
                    duration: 6000
                });
                return;
            }
        } else if (step == 4) {
            if (data.sindico && typeof(parseInt(data.sindico)) == "number" && data.sindico > 0) {
                var sindico = data.sindico;
                var dataP = {};

                var telefone = localStorage.getItem("form_telephone");
                var operacao = localStorage.getItem("form_shop");
                var alojamento = localStorage.getItem("form_accommodation");

                localStorage.removeItem("form_telephone");
                localStorage.removeItem("form_shop");
                localStorage.removeItem("form_accommodation");
                localStorage.removeItem("form_step");

                if (telefone && operacao && alojamento) {
                    dataP.telefone = telefone;
                    dataP.operacao = operacao;
                    dataP.alojamento = alojamento;
                    dataP.sindico = sindico;

                    $.ajax({
                        type: "POST",
                        url: "./createRequest.php",
                        data: dataP,
                        success: function(result) {
                            console.log(result);

                            if (result.gerado) {
                                var codigo = result.codigo;

                                tata.success('Solicitação processada',
                                    'A solicitação foi processada.', {
                                        duration: 1000
                                    });

                                setTimeout(() => {
                                    window.location.href = `../finalizado/?codigoSolicitacao=${codigo}`;
                                }, 1000);

                            } else {
                                tata.error('Erro desconecido',
                                'Ocorreu um erro desconecido ao processar a solicitação.', {
                                    duration: 3000
                                });
                            }
                        },
                        error: function(result) {
                            tata.error('Erro desconecido',
                                'Ocorreu um erro desconecido ao processar a solicitação.', {
                                    duration: 3000
                                });
                        }
                    });

                } else {
                    tata.error('Informações inválidas',
                        'Ocorreu um erro ao processar as informações. Reinicie a solicitação.', {
                            duration: 3000
                        });

                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                    return;
                }

            } else {
                tata.error('Síndico inválido', 'O síndico informado é inválido', {
                    duration: 6000
                });
                return;
            }
        }
    })


    $(document).ready(function() {
        $('#input_telefone').mask('(00) 00000-0000');

        $(`#step-${step}`).show();
    });

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


    $('#continue-button').on('click', () => {});

    $('#btn_selecionarOperacao').on('click', () => {
        abrirModal('modal_operacoes');
    })

    $('#btn_selecionarMoradia').on('click', () => {
        abrirModal('modal_moradias');
    })

    $('#btn_selecionarSindico').on('click', () => {
        abrirModal('modal_sindicos');
    })

    function getRandomNum(minimo, maximo) {
        return Math.floor(Math.random() * (maximo - minimo + 1)) + minimo;
    }

    function removerAcentos(texto) {
        return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    function filtrarModal(tipo) {
        var textoFiltro = removerAcentos(document.getElementById('input_' + tipo + 'Filtro').value.toLowerCase());

        var listaProdutos = document.getElementById('painel-' + tipo).getElementsByTagName('li');

        if (listaProdutos.length > 0) {
            for (var i = 0; i < listaProdutos.length; i++) {
                var nomeProduto = listaProdutos[i].querySelector('#span_' + tipo + 'Nome').textContent.toLowerCase();

                if (removerAcentos(nomeProduto).includes(textoFiltro)) {
                    listaProdutos[i].style.display = 'block';
                } else {
                    listaProdutos[i].style.display = 'none';
                }
            }
        }
    }

    function selecionarOperacao(operacao) {
        closeModal('modal_operacoes');
        $('#btn_selecionarOperacao').text(operacao);

        data.operacao = operacao;
    }

    function selecionarMoradia(id, endereco) {
        closeModal('modal_moradias');
        $('#btn_selecionarMoradia').text(endereco);

        if(id && typeof(parseInt(id)) == "number") {
            data.alojamento = parseInt(id);
        }
    }

    function selecionarSindico(id, nome) {
        closeModal('modal_sindicos');
        $('#btn_selecionarSindico').text(nome);

        if(id && typeof(parseInt(id)) == "number") {
            data.sindico = parseInt(id);
        }
    }
    </script>

</body>

</html>