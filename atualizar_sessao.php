<?php
if (!isset($_SESSION)) {
    session_start();
}

// Verifique se os dados foram enviados por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Verifique se as variáveis estão definidas
  if (isset($_POST['month']) && isset($_POST['year'])) {
    // Obtenha os valores enviados
    $selectedMonth = $_POST['month'];
    $selectedYear = $_POST['year'];

    // Atualize as variáveis de sessão
    $_SESSION['month'] = $selectedMonth;
    $_SESSION['year'] = $selectedYear;

    // Responda com um código de status 200 (OK)
    http_response_code(200);
    echo 'Variáveis de sessão atualizadas com sucesso.';
  } else {
    // Responda com um código de status 400 (Bad Request) - Dados incompletos
    http_response_code(400);
    echo 'Dados incompletos. Não foi possível atualizar as variáveis de sessão.';
  }
} else {
  // Responda com um código de status 405 (Method Not Allowed) - Método não permitido
  http_response_code(405);
  echo 'Método não permitido. Utilize o método POST para atualizar as variáveis de sessão.';
}
?>