<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    $retorno = array();

    if(sizeof($data['listaLancamentos']) < 1) {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($retorno);
        exit();
    }

    $sql = "SELECT * FROM razao WHERE ";

    $documentosProcurados = array();
    $documentosEncontrados = array();

    $i = 0;
    foreach ($data['listaLancamentos'] as $lancamento) {
        $lancamento = mysqli_real_escape_string($mysqli, $lancamento);

        array_push($documentosProcurados, $lancamento);

        if(preg_match('/^[0-9]+$/', $lancamento)) {
            if($i > 0) $sql .= " OR ";
            $sql .= "documento='" . $lancamento . "'";
    
            $i++;
        }
    }

    if($i < 1) {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($retorno);
        exit();
    }

    $result = $mysqli->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $documento = $row['documento'];
            array_push($documentosEncontrados, $documento);

            $valorLiquido = floatval($row['valor_unitario']);

            $arquivoComprovante = $row['comprovante_pagamento'];

            $fornecedorCodigo = $row['cod_fornecedor'];
            $fornecedorLoja = $row['loja_fornecedor'];
            $fornecedorNome = $row['nome_fornecedor'];

            $filialCodigo = $row['cod_filial'];
            $filialNome = $row['descr_filial'];

            $naturezaCodigo = $row['natureza'];
            $naturezaNome = $row['descr_natureza'];

            $borderoNumero = $row['numero_bordero'];
            $borderoData = "-";
            if(isset($row['data_bordero'])) $borderoData = date_format(new DateTime($row['data_bordero']), "Y-m-d\TH:i:s\Z");

            $dataEmissao = "-";
            if(isset($row['data_emissao'])) $dataEmissao = date_format(new DateTime($row['data_emissao']), "Y-m-d\TH:i:s\Z");

            $dataVencimento = "-";
            if(isset($row['data_vencimento'])) $dataVencimento = date_format(new DateTime($row['data_vencimento']), "Y-m-d\TH:i:s\Z");

            $dataBaixa = "-";
            if(isset($row['data_baixa'])) $dataBaixa = date_format(new DateTime($row['data_baixa']), "Y-m-d\TH:i:s\Z");

            $dataAtualizacao = "-";
            if(isset($row['data_atualizacao'])) $dataAtualizacao = date_format(new DateTime($row['data_atualizacao']), "Y-m-d\TH:i:s\Z");

            $contrato = $row['contrato'];


            array_push($retorno, array(
                "documento" => $documento,
                "existente" => true,
                "valor" => $valorLiquido,
                "contrato" => $contrato,
                "fornecedor" => array(
                    "codigo" => $fornecedorCodigo,
                    "loja" => sprintf('%04d', $fornecedorLoja),
                    "descricao" => $fornecedorNome
                ),
                "filial" => array(
                    "codigo" => $filialCodigo,
                    "descricao" => $filialNome
                ),
                "natureza" => array(
                    "codigo" => $naturezaCodigo,
                    "descricao" => $naturezaNome
                ),
                "bordero" => array(
                    "numero" => $borderoNumero,
                    "data" => $borderoData
                ),
                "status" => array(
                    "emissao" => $dataEmissao,
                    "vencimento" => $dataVencimento,
                    "baixa" => $dataBaixa
                ),
                "comprovante" => $arquivoComprovante,
                "atualizacao" => $dataAtualizacao
            ));
        }
    }

    $naoEncontrados = array_diff($documentosProcurados, $documentosEncontrados);

    foreach ($naoEncontrados as $documento) {
        array_push($retorno, array(
            "documento" => $documento,
            "existente" => false,
        ));
    }

    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($retorno);
    exit();

} else {
    http_response_code(400);
    exit();
}
?>