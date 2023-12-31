<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    http_response_code(401);
    header("Content-Type: application/json");
    echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Usuário não autorizado."));
    exit();
}

include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

$baseYear = date('Y');

if(isset($_SESSION['year'])) {
    if($_SESSION['year'] == 2023 || $_SESSION['year'] == 2024) {
        $baseYear = mysqli_real_escape_string($mysqli, $_SESSION['year']);
    }
}

if(isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // Acessa as propriedades do arquivo
    $originalFileName = $file['name'];
    $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
    $realFileName = str_replace('.' . $fileExtension, '', $originalFileName);
    
    if($fileExtension != "pdf") {
        header("Content-Type: application/json");
        echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Apenas arquivos .pdf são permitidos."));
        http_response_code(400);
        exit();
    }
    $fileNewId = uniqid();
    $fileName = $fileNewId . '.' . $fileExtension;
    $fileType = $file['type'];
    $fileSize = $file['size'];
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];


    $fileType = 'desconhecido';
    $fileData = array();

    if (strpos($realFileName, '.') !== false) {
        $splitArray = explode('.', $realFileName);
    
        if (count($splitArray) == 3) {
            $forIndex = 0;

            foreach ($splitArray as $separ) {
                $forIndex++;

                if (is_numeric($separ)) {
                    if ((strlen($separ) == 2 && $forIndex == 1)) {
                        $fileType = "comprovante";
                        $fileData['comprov_day'] = $separ;

                    } elseif(strlen($separ) == 2 && $forIndex == 2) {
                        $fileData['comprov_month'] = $separ;

                    } elseif (strlen($separ) == 4 && $forIndex == 3) {
                        $fileType = "comprovante";
                        $fileData['comprov_year'] = $separ;

                    } else {
                        $fileType = "desconhecido";
                        break;
                    }
                } else {
                    $fileType = "desconhecido";
                    break;
                }
            }
        } else {
            $fileType = "desconhecido";
        }
    }
    
    if (strpos($realFileName, ' - ') !== false && $fileType == "desconhecido") { // 400313 - 0001 - 558721 - 05-06 - Cond - R. Voluntarios da Patria, 475 ap 401
        $splitArray = explode(' - ', $realFileName);
    
        if (count($splitArray) >= 6) {
            $forIndex = 0;
    
            foreach ($splitArray as $separ) {
                $forIndex++;
    
                if ($forIndex == 1 && strlen($separ) == 6 && is_numeric($separ)) {
                    $fileType = "boleto";
                    $fileData['boleto_fornecedor'] = $separ;
                    
                } elseif ($forIndex == 2 && strlen($separ) == 4 && is_numeric($separ)) {
                    $fileType = "boleto";
                    $fileData['boleto_loja'] = $separ;

                } elseif ($forIndex == 3 && strlen($separ) == 6 && is_numeric($separ)) {
                    $fileType = "boleto";
                    $fileData['boleto_lancamento'] = $separ;

                } elseif ($forIndex == 4 && strlen($separ) == 5 && strpos($separ, "-") !== false) {
                    $fileType = "boleto";
                    $fileData['boleto_vencimento'] = $separ;

                } elseif ($forIndex == 5 && $separ == "Cond") {
                    $fileType = "boleto";

                } elseif ($forIndex < 6) {
                    $fileType = "desconhecido";
                    break;

                } elseif ($forIndex >= 6) {
                    if(isset($fileData['boleto_endereco'])) {
                        $fileData['boleto_endereco'] .= $separ;

                    } else {
                        $fileData['boleto_endereco'] = $separ;
                    }
                }
            }
        } else {
            $fileType = "desconhecido";
        }
    }

    if($fileType != "comprovante" && $fileType != "boleto") {
        header("Content-Type: application/json");
        echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Erro ao identificar o arquivo."));
        http_response_code(400);
        exit();
    }

    if($fileType == "comprovante") {
        $day = $fileData['comprov_day'];
        $month = $fileData['comprov_month'];
        $year = $fileData['comprov_year'];

        if(!isset($day) || !isset($month) || !isset($year)) {
            header("Content-Type: application/json");
            echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Erro ao verificar o comprovante."));
            http_response_code(400);
            exit();
        }

        $hash = hash_file('sha256', $fileTmpName);

        $queryCheck = "SELECT * FROM comprovantes WHERE hash='{$hash}';";
        $resultCheck = mysqli_query($mysqli, $queryCheck);
        if(mysqli_num_rows($resultCheck) > 0) {
            header("Content-Type: application/json");
            echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Comprovante duplicado."));
            http_response_code(400);
            exit();
        }

        $referencia = date_create("$year-$month-$day")->format('Y-m-d');

        $query = "INSERT INTO comprovantes (`nome_interno`, `codigo_interno`, `nome_original`, `tipo_arquivo`, `hash`, `dia`, `mes`, `ano`, `referencia`) VALUES ('{$fileName}', '{$fileNewId}', '{$originalFileName}', '{$fileExtension}', '{$hash}', '{$day}', '{$month}', '{$year}', '{$referencia}');";
        $result = mysqli_query($mysqli, $query);

        if ($result) {
            $destination = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $fileName;
            move_uploaded_file($fileTmpName, $destination);

            header("Content-Type: application/json");
            echo json_encode(array("salvo" => true, "tipo_arquivo" => "comprovante", "arquivo_nome_original" => $originalFileName, "arquivo_codigo_interno" => $fileNewId, "arquivo_nome_interno" => $fileName, "competencia" => $day . "/" . $month . "/" . $year));
            http_response_code(200);
            exit();

        } else {
            echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Erro de servidor ao inserir dados do boleto no banco de dados."));
            http_response_code(500);
            exit();
        }
    } else if ($fileType == "boleto") {
        $fornecedor = $fileData['boleto_fornecedor'];
        $loja = $fileData['boleto_loja'];
        $lancamento = $fileData['boleto_lancamento'];
        $endereco = $fileData['boleto_endereco'];

        $vencimentoString = $fileData['boleto_vencimento'];
        $diaVencimento = 0;
        $mesVencimento = 0;

        if(strlen($vencimentoString) != 5) {
            header("Content-Type: application/json");
            echo json_encode(array("salvo" => false, "tipo_arquivo" => "comprovante", "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Erro ao identificar o arquivo."));
            http_response_code(400);
            exit();
        }
        $splitArray = explode('-', $vencimentoString);

        if(count($splitArray) != 2) {
            header("Content-Type: application/json");
            echo json_encode(array("salvo" => false, "tipo_arquivo" => "comprovante", "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Erro ao identificar o arquivo."));
            http_response_code(400);
            exit();
        }

        $forIndex = 0;
        foreach ($splitArray as $separ) {
            $forIndex++;

            if(strlen($separ) == 2 && is_numeric($separ)) {
                if($forIndex == 1) {
                    $diaVencimento = $separ;

                } else if($forIndex == 2) {
                    $mesVencimento = $separ;

                } else {
                    header("Content-Type: application/json");
                    echo json_encode(array("salvo" => false, "tipo_arquivo" => "comprovante", "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Erro ao identificar o arquivo."));
                    http_response_code(400);
                    exit();
                }
            }
        }

        $expireDate = new DateTime("$baseYear-$mesVencimento-$diaVencimento");
        $expireDateFormatted = $expireDate->format('Y-m-d');

        if(!isset($diaVencimento) || !isset($mesVencimento)  || !isset($vencimentoString) || !isset($endereco) || !isset($fornecedor) || !isset($loja)  || !isset($lancamento)) {
            header("Content-Type: application/json");
            echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Falha ao verificar o boleto."));
            http_response_code(400);
            exit();
        }
        
        $queryClassification = "SELECT r.documento, r.contrato AS contrato_razao, a.operacao, a.id, a.contrato_totvs, a.endereco FROM razao r LEFT JOIN alojamentos a ON r.contrato=a.contrato_totvs WHERE r.documento = {$lancamento}; ";
        $resultClassification = mysqli_query($mysqli, $queryClassification);
        if(mysqli_num_rows($resultClassification) != 1) {
            header("Content-Type: application/json");
            echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "A razão atual não contém o lançamento."));
            http_response_code(400);
            exit();
        }

        $razaoData = mysqli_fetch_assoc($resultClassification);
        if(!isset($razaoData) || !isset($razaoData['id']) || !isset($razaoData['endereco'])) {
            header("Content-Type: application/json");
            echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Moradia não encontrada á partir do numero de lançamento."));
            http_response_code(400);
            exit();
        }
        $houseId = $razaoData['id'];

        $queryCheck = "SELECT * FROM boletos WHERE id_alojamento='{$houseId}' AND DAY(data_vencimento) = $diaVencimento AND MONTH(data_vencimento) = $mesVencimento;";
        $resultCheck = mysqli_query($mysqli, $queryCheck);
        if(mysqli_num_rows($resultCheck) > 0) {
            header("Content-Type: application/json");
            echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Boleto duplicado."));
            http_response_code(400);
            exit();
        }

        $query = "INSERT INTO boletos (`codigo_interno`, `nome_interno`, `nome_original`, `tipo_arquivo`, `endereco`, `id_alojamento`, `fornecedor`, `loja`, `lancamento`, `data_vencimento`, `data_inclusao`, `id_autor_inclusao`) VALUES ('{$fileNewId}', '{$fileName}', '{$originalFileName}', '{$fileExtension}', '{$endereco}', '{$houseId}', '{$fornecedor}', '{$loja}', '{$lancamento}', '{$expireDateFormatted}', current_timestamp(), '{$_SESSION['USER_ID']}');";
        $result = mysqli_query($mysqli, $query);
        

        if ($result) {
            $destination = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $fileName;
            move_uploaded_file($fileTmpName, $destination);

            header("Content-Type: application/json");
            echo json_encode(array("salvo" => true, "tipo_arquivo" => "boleto", "id_moradia" => $houseId, "endereco_moradia" => $razaoData['endereco'], "arquivo_nome_original" => $originalFileName, "arquivo_codigo_interno" => $fileNewId, "arquivo_nome_interno" => $fileName, "vencimento" => $expireDateFormatted, "titulo" => $lancamento));
            http_response_code(200);
            exit();

        } else {
            header("Content-Type: application/json");
            echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Erro de servidor."));
            http_response_code(500);
            exit();
        }

    } else {
        header("Content-Type: application/json");
        echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Erro ao identificar o arquivo."));
        http_response_code(400);
        exit();
    }
}
?>