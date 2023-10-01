<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if (!isset($_SESSION)) {
    session_start();
}

?>

<header>
    <nav>
        <div class="logo" href="/">
            <img class="madero-logo" src="/assets/images/madero-white.png" alt="">
            <span class="name" onclick="window.location.href='/'">LANÇAMENTOS</span>
        </div>
        <div class="mobile-menu">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>

        <ul class="nav-list">
            <li><a href="/ferramentas/lancamentos/"><i class='bx bxs-barcode'></i> Por Lançamento</a></li>
            <li><a href="/ferramentas/lancamentos/detalhado/cc/"><i class='bx bxs-store'></i> Por Centro de Custo</a></li>
            <li><a href="/ferramentas/lancamentos/detalhado/contrato/"><i class='bx bx-paperclip'></i> Por Contrato</a></li>
        </ul>
    </nav>
</header>