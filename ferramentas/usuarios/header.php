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
            <span class="name" onclick="window.location.href='/'">USUÁRIOS</span>
        </div>
        <div class="mobile-menu">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>

        <ul class="nav-list">
            <li><a href="/"><i class='bx bxs-tachometer'></i> Início</a></li>
            <li><a href="/"><i class='bx bxs-home'></i> Moradias</a></li>
            <li><a href="/" class="special-btn"><i class='bx bx-envelope'></i> SOLICITAÇÕES</a></li>
        </ul>
    </nav>
</header>