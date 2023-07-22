<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    header("Content-Type: application/json");
    echo json_encode(array("sucesso" => false, "mensagem" =>  "Usuário não autorizado."));
    http_response_code(401);
    exit();
}

include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se o arquivo foi enviado corretamente
    if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK && isset($_POST['idBoleto'])) {
        $tempFilePath = $_FILES['pdfFile']['tmp_name'];
        $newFileCode = uniqid();
        $newFilePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $newFileCode . '.pdf';

        $idBoleto = mysqli_real_escape_string($mysqli, $_POST['idBoleto']);

        $updateQuery = "UPDATE boletos SET arquivo_comprovante='{$newFileCode}.pdf' WHERE `id`={$idBoleto};";
        $result = mysqli_query($mysqli, $updateQuery);
        if ($result) {
            // Move o arquivo para o diretório de destino
            if (move_uploaded_file($tempFilePath, $newFilePath)) {
                // Arquivo salvo com sucesso
                header("Content-Type: application/json");
                echo json_encode(array("sucesso" => true, "mensagem" =>  "Comprovante de pagamento salvo."));
                http_response_code(200);
                exit();
            } else {
                // Ocorreu um erro ao mover o arquivo
                header("Content-Type: application/json");
                echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao salvar o comprovante de pagamento."));
                http_response_code(500);
                exit();
            }
        } else {
            header("Content-Type: application/json");
            echo json_encode(array("sucesso" => false, "mensagem" =>  "Ocorreu um erro ao adicionar o comprovante de pagamento na base de dados."));
            http_response_code(500);
            exit();
        }

    } else {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "mensagem" =>  "Arquivo ou dados não recebidos."));
        http_response_code(400);
        exit();
    }
} else {
    http_response_code(405);
    exit();
}
?>