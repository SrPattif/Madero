<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if (!isset($_SESSION)) {
    session_start();
}

?>

<header>
    <nav>
        <div class="logo">
            <a href="/">
                <img class="madero-logo" src="/assets/images/madero-white.png" alt="">
                <span class="name">LANÇAMENTOS</span>
            </a>
        </div>
        <div class="mobile-menu">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>

        <ul class="nav-list">
            <li><a href="/ferramentas/lancamentos/"><i class='bx bxs-home'></i> Pesquisa Padrão</a></li>
            <li><a href="/ferramentas/lancamentos/detalhado/"><i class='bx bxs-rocket'></i> Pesquisa Avançada</a></li>
        </ul>
    </nav>
</header>