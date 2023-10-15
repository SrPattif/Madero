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
            <span class="name" onclick="window.location.href='/'">SÍNDICOS</span>
        </div>
        <div class="mobile-menu">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>

        <ul class="nav-list">
            <li><a href="/ativos/sindicos/"><i class='bx bxs-home'></i> Início</a></li>
            <li><a href="/ativos/sindicos/solicitacoes/"><i class='bx bxs-paper-plane'></i> Solicitações</a></li>
            <li><a href="/ativos/sindicos/moradias/"><i class='bx bxs-home'></i> Síndicos</a></li>
        </ul>
    </nav>
</header>