<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if (!isset($_SESSION)) {
    session_start();
}

$month = date('n');
$year = date('Y');

if(isset($_SESSION['year'])) {
    if($_SESSION['year'] == 2023 || $_SESSION['year'] == 2024) {
        $year = mysqli_real_escape_string($mysqli, $_SESSION['year']);
    }
}

if(isset($_SESSION['month'])) {
    if($_SESSION['month'] > 0 && $_SESSION['month'] <= 12) {
        $month = mysqli_real_escape_string($mysqli, $_SESSION['month']);
    }
}

?>

<header>
    <nav>
        <div class="logo" href="/">
            <img class="madero-logo" src="/assets/images/madero-white.png" alt="">
            <span class="name" onclick="window.location.href='/'">Reembolsos</span>
        </div>
        <div class="mobile-menu">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>

        <ul class="nav-list">
            <li><a href="/moradias/reembolsos/"><i class='bx bxs-tachometer'></i> Início</a></li>
            <li><a href="/moradias/reembolsos/alojamentos/"><i class='bx bxs-home'></i> Moradias</a></li>
            <li><a href="/moradias/reembolsos/"><i class='bx bxs-report'></i> Relatórios</a></li>
            <li><a href="/moradias/reembolsos/arquivos/"><i class='bx bxs-file-blank'></i> Arquivos</a></li>
            <li><a href="/moradias/reembolsos/medicoes/iniciar/" class="special-btn"><i class='bx bx-clipboard'></i> MEDIÇÕES</a></li>
            <li><a href="/moradias/reembolsos/solicitacoes/" class="special-btn"><i class='bx bx-envelope'></i> SOLICITAÇÕES</a></li>
        </ul>
    </nav>
</header>

<div class="date-selectors">
    <div class="select">
        <select id="select-year">
            <option value="reject">Ano</option>
            <option value="2023" <?php if($year == 2023) echo('selected'); ?>>2023</option>
            <option value="2024" <?php if($year == 2024) echo('selected'); ?>>2024</option>
        </select>
        <div class="select_arrow">
        </div>
    </div>

    <div class="select">
        <select id="select-month">
            <option value="reject">Mês</option>
            <option value="1" <?php if($month == 1) echo('selected'); ?>>Janeiro</option>
            <option value="2" <?php if($month == 2) echo('selected'); ?>>Fevereiro</option>
            <option value="3" <?php if($month == 3) echo('selected'); ?>>Março</option>
            <option value="4" <?php if($month == 4) echo('selected'); ?>>Abril</option>
            <option value="5" <?php if($month == 5) echo('selected'); ?>>Maio</option>
            <option value="6" <?php if($month == 6) echo('selected'); ?>>Junho</option>
            <option value="7" <?php if($month == 7) echo('selected'); ?>>Julho</option>
            <option value="8" <?php if($month == 8) echo('selected'); ?>>Agosto</option>
            <option value="9" <?php if($month == 9) echo('selected'); ?>>Setembro</option>
            <option value="10" <?php if($month == 10) echo('selected'); ?>>Outubro</option>
            <option value="11" <?php if($month == 11) echo('selected'); ?>>Novembro</option>
            <option value="12" <?php if($month == 12) echo('selected'); ?>>Dezembro</option>
        </select>
        <div class="select_arrow">
        </div>
    </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Captura os elementos select do mês e do ano
    var selectMonth = document.getElementById("select-month");
    var selectYear = document.getElementById("select-year");

    // Adiciona um evento de mudança aos selects
    selectMonth.addEventListener("change", updateSessionValues);
    selectYear.addEventListener("change", updateSessionValues);

    // Função para atualizar as variáveis de sessão
    function updateSessionValues() {
      // Obtém os valores selecionados do mês e do ano
      var selectedMonth = selectMonth.value;
      var selectedYear = selectYear.value;

      // Cria um objeto FormData para enviar os valores por AJAX
      var formData = new FormData();
      formData.append('month', selectedMonth);
      formData.append('year', selectedYear);

      // Cria uma nova requisição AJAX
      var request = new XMLHttpRequest();

      // Configura a requisição para enviar os dados por POST ao script PHP
      request.open('POST', '/atualizar_sessao.php');
      
      // Define o callback a ser chamado quando a requisição for concluída
      request.onload = function() {
        if (request.status === 200) {
          // Atualização da sessão concluída com sucesso
          console.log('Variáveis de sessão atualizadas com sucesso.');
          window.location.reload();
        } else {
          // Ocorreu um erro ao atualizar a sessão
          console.log('Erro ao atualizar as variáveis de sessão.');
        }
      };

      // Envia a requisição com os dados do FormData
      request.send(formData);
    }
  });
</script>