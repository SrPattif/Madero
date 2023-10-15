<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    http_response_code(401);
    header("Content-Type: application/json");
    echo json_encode(array("salvo" => false, "mensagem" => "Usuário não autorizado."));
    exit();
}

$userId = $_SESSION['USER_ID'];

include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if(isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // Acessa as propriedades do arquivo
    $originalFileName = $file['name'];
    $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
    $realFileName = str_replace('.' . $fileExtension, '', $originalFileName);
    
    if($fileExtension != "png" && $fileExtension != "jpg" && $fileExtension != "jpeg") {
        header("Content-Type: application/json");
        echo json_encode(array("salvo" => false, "mensagem" => "Tipo de arquivo inválido."));
        http_response_code(400);
        exit();
    }
    $fileNewId = uniqid();
    $fileName = $fileNewId . '.' . $fileExtension;
    $fileTmpName = $file['tmp_name'];
    
    $query = "UPDATE usuarios SET imagem_perfil='{$fileName}' WHERE id={$userId};";
    $result = mysqli_query($mysqli, $query);

    if ($result) {
        $destination = $_SERVER['DOCUMENT_ROOT'] . '/uploads/pfp/' . $fileName;
        move_uploaded_file($fileTmpName, $destination);

        header("Content-Type: application/json");
        echo json_encode(array("salvo" => true, "mensagem" => "Imagem de perfil alterada com sucesso."));
        http_response_code(200);
        exit();

    } else {
        echo json_encode(array("salvo" => false, "arquivo_nome_original" => $originalFileName, "descricao_erro" => "Erro de servidor ao inserir dados do boleto no banco de dados."));
        http_response_code(500);
        exit();
    }
}
?>