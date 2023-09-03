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
    require($_SERVER['DOCUMENT_ROOT'] . '/ferramentas/lancamentos/header.php');
    ?>

    <main>
        <div class="page-content">
            <div class="testing-card">
                <div class="card-header">
                    <h3><i class='bx bx-test-tube bx-tada bx-rotate-270' ></i> Módulo em Testes</h3>
                </div>
                Este módulo ainda está em fase de testes e <span class="bold">pode apresentar erros, problemas ou inconsistências</span>.
                <br>Certifique-se de conferir todas as informações fornecidas antes de prosseguir.
                <br>
                <br>Se você <span class="bold">encontrar algum erro</span> ou divergência, <span class="bold">entre em contato</span> com o administrador do sistema.
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Consultar Lançamentos</h3>
                    <!--
                    <div class="option-list">
                        <div class="option" onclick="abrirModal('modal_cadastrarUsuario')">
                            <i class='bx bxs-user-plus'></i> CADASTRAR USUÁRIO
                        </div>
                    </div>
                    -->
                </div>
                <div class="card-description">
                    <span>Insira abaixo o número do lançamento que deseja consultar. Você pode inserir múltiplos títulos ao mesmo tempo. Para isso, basta separá-los com espaço.<br>Quando terminar a digitação, pressione <span class="key-input"><i class='bx bxs-keyboard'></i> ENTER</span> ou clique em <span class="key-input"><i class='bx bx-chevrons-right' ></i> PROSSEGUIR</span>.</span>
                </div>

                <div class="input-lcto">
                    <input type="text" id="input_lancamento" placeholder="Digite o número do lançamento">
                    <div class="button" onclick="manusearInput();"><i class='bx bx-chevrons-right' ></i></div>
                </div>
                <div class="mensagem-inicial" id="div_msgInicial">
                    <i class='bx bx-chevrons-up'></i>
                    Insira acima o número do lançamento que deseja consultar.
                    <i class='bx bx-chevrons-up'></i>
                </div>
                <div class="mensagem-intermediaria" id="div_msgIntermediaria" style="display: none;">
                    <div class="simple-button" onclick="limparLancamentos()">LIMPAR LANÇAMENTOS</div>
                </div>
                <div class="lista-lancamentos">
                    <ul id="ul_lancamentos">
                    </ul>
                </div>
                <div id="resultados-lancamentos">
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

    var listaLancamentos = [];

    function gerenciarListaLancamentos(lancamento, atualizar = false) {
        if (listaLancamentos.includes(lancamento)) {
            listaLancamentos = listaLancamentos.filter(function(item) {
                return item !== lancamento;
            });
        } else {
            listaLancamentos.push(lancamento);
        }

        if(atualizar) {
            atualizarLista();
        }
    }

    function manusearInput() {
        const inputLancamento = document.getElementById('input_lancamento');
        const inputNumero = inputLancamento.value;

        inputNumero.split(' ').forEach(titulo => {
            if (titulo.trim() === '') {
                return;
            }

            gerenciarListaLancamentos(titulo);
        });
        

        inputLancamento.value = "";
        atualizarLista();
    }

    function atualizarLista() {
        const resultadosDiv = document.getElementById('resultados-lancamentos');
        const ulLancamentos = document.getElementById('ul_lancamentos');

        while (resultadosDiv.firstChild) {
            resultadosDiv.removeChild(resultadosDiv.firstChild);
        }
        while (ulLancamentos.firstChild) {
            ulLancamentos.removeChild(ulLancamentos.firstChild);
        }

        $.ajax({
            url: 'consultarLancamentos.php',
            type: 'POST',
            data: JSON.stringify({
                listaLancamentos
            }),
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.length > 0) {
                    $('#div_msgIntermediaria').show();
                    $('#div_msgInicial').hide();

                } else {
                    $('#div_msgIntermediaria').hide();
                    $('#div_msgInicial').show();
                }
                response.forEach(lancamento => {
                    var encontrado = lancamento.existente;
                    var documento = lancamento.documento;

                    var li_lancamento = document.createElement('li');
                    li_lancamento.innerHTML = `<span onclick="gerenciarListaLancamentos('${documento}', true)">${documento} <i class="bx bx-x"></i></span>`;
                    ulLancamentos.appendChild(li_lancamento);

                    if(encontrado) {
                        var contrato = lancamento.contrato;
                        var filial = lancamento.filial;
                        var fornecedor = lancamento.fornecedor;
                        var valor = lancamento.valor;
                        var bordero = lancamento.bordero;
                        var natureza = lancamento.natureza;
                        var status = lancamento.status;
                        var atualizado = lancamento.atualizacao;
                        var comprovante = lancamento.comprovante;

                        // HTML que você quer inserir
                        var htmlToInsert = `
                        <div class="card">
                            <h2>Título ${documento}</h2>
                            <div class="card-body" style="font-size: 1rem;">
                                <div class="dados-lancamento">
                                    Valor: <span class="bold">R$ ${numberFormat(valor)}</span>
                                    <br>
                                    Fornecedor: <span class="bold">${fornecedor.loja} : ${fornecedor.codigo} : ${fornecedor.descricao}</span>
                                    <br>
                                    <br>
                                    Filial: <span class="bold">${filial.codigo} : ${filial.descricao}</span>
                                    <br>
                                    Natureza: <span class="bold">${natureza.codigo} : ${natureza.descricao}</span>
                                    <br>
                                    Contrato vinculado: <span class="bold">${contrato}</span>
                                </div>
                                <div class="card">
                                    <h3>Datas</h3>
                                    Data de emissão: <span class="bold">${formatISODateToCustomFormat(status.emissao)}</span>
                                    <br>
                                    Data de vencimento: <span class="bold">${formatISODateToCustomFormat(status.vencimento)}</span>
                                    <br>
                                    Data de baixa: <span class="bold">${formatISODateToCustomFormat(status.baixa)}</span>
                                </div>
                                <div class="card">
                                    <h3>Dados de Borderô</h3>
                                    Número do borderô: <span class="bold">${bordero.numero > 0 ? bordero.numero : '-'}</span>
                                    <br>
                                    Data do borderô: <span class="bold">${formatISODateToCustomFormat(bordero.data)}</span>
                                </div>
                                `;

                        if(comprovante) {
                            htmlToInsert += `
                            <div class="comprovante" onclick="window.open('/uploads/${comprovante}', '_blank').focus();">
                                <b><i class='bx bxs-file-pdf'></i> Comprovante de Pagamento</b>
                            </div>`;

                        } else {
                            htmlToInsert += `
                            <div class="comprovante">
                                <span class="no-file">Comprovante de pagamento não encontrado.</span>
                                <div class="button" style="width: 45%; margin: 1em auto 0 auto;" onclick="apurarComprovante(${documento})">
                                    APURAR COMPROVANTE
                                </div>
                            </div>
                            `;
                        }

                        htmlToInsert += `
                                <div class="data-atualizacao"><i class='bx bx-sync'></i> Atualizado em ${formatISODateToCustomFormat(atualizado)}</div>
                            </div>
                        </div>
                        `;

                        // Cria um elemento div temporário para conter o HTML a ser inserido
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = htmlToInsert;

                        // Insere o conteúdo do tempDiv como o último item da div alvo
                        resultadosDiv.appendChild(tempDiv);

                    } else {
                        // HTML que você quer inserir
                        var htmlToInsert = `
                        <div class="card">
                            <h2>Título ${documento}</h2>
                            <div class="card-body" style="font-size: 1rem;">
                                <div class="card error-card">
                                    <h3 class="title">Documento não encontrado</h3>
                                    O documento informado não foi encontrado na base de dados.
                                </div>
                            </div>
                        </div>
                        `;

                        // Cria um elemento div temporário para conter o HTML a ser inserido
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = htmlToInsert;

                        // Insere o conteúdo do tempDiv como o último item da div alvo
                        resultadosDiv.appendChild(tempDiv);
                    }
                });

            },
            error: function() {
                console.log('Erro ao obter dados do lançamento.')
            }
        });

    }

    function formatISODateToCustomFormat(isoDateTime) {
        if(!isValidISODate(isoDateTime)) {
            return "-";
        }
        // Criar um objeto Date a partir da data ISO
        var jsDate = new Date(isoDateTime);

        // Ajustar para o fuso horário local
        var timeZoneOffset = jsDate.getTimezoneOffset(); // Em minutos
        jsDate.setMinutes(jsDate.getMinutes() + timeZoneOffset);

        // Função para adicionar um zero à esquerda, se necessário
        function addLeadingZero(number) {
            return number < 10 ? "0" + number : number;
        }

        // Obter o dia, mês e ano da data
        var day = jsDate.getDate();
        var month = jsDate.getMonth() + 1; // Os meses em JavaScript são baseados em zero
        var year = jsDate.getFullYear();

        // Formatando para "dd/mm/aaaa"
        var formattedDate = `${addLeadingZero(day)}/${addLeadingZero(month)}/${year}`;

        return formattedDate;
    }
    
    function isValidISODate(dateStr) {
        const date = new Date(dateStr);
        // Verifica se o valor não é inválido e se não é igual a "-0001-11-30T00:00:00Z"
        return !isNaN(date) && date.toISOString() !== "-0001-11-30T00:00:00.000Z";
    }

    $(document).ready(function() {
        $(document).on("keyup", function(event) {
            if (event.keyCode === 13) {
                if (document.getElementById('input_lancamento').value != "") {
                    manusearInput();
                }
            }
        });
    });

    function limparLancamentos() {
        listaLancamentos = [];
        atualizarLista();
    }

    function numberFormat(number, decimals = 2, decimalSeparator = ',', thousandSeparator = '.') {
        const fixedNumber = number.toFixed(decimals);
        const [integerPart, decimalPart] = fixedNumber.split('.');
        
        const formattedInteger = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);

        return `${formattedInteger}${decimalSeparator}${decimalPart}`;
    }

    function apurarComprovante(titulo) {
        $.ajax({
            type: "POST",
            url: "./apurarComprovante.php",
            data: {
                titulo: titulo,
            },
            success: function(result) {
                tata.success('Comprovante apurado',
                    'O comprovante de pagamento foi apurado com sucesso.', {
                        duration: 6000
                    });

                setTimeout(() => {
                    atualizarLista()
                }, 500);
                return;
            },
            error: function(result) {
                tata.error('Um erro ocorreu',
                    'Ocorreu um erro ao tentar apurar o comprovante de pagamento. (' + result
                    .responseJSON.descricao_erro + ')', {
                        duration: 6000
                    });
            }
        });
    }

    </script>

</body>

</html>