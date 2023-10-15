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
                <span class="name">USUÁRIOS</span>
            </a>
        </div>
        <div class="mobile-menu">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>

        <ul class="nav-list">
            <li><a href="/ferramentas/usuarios/"><i class='bx bxs-tachometer'></i> Início</a></li>
        </ul>
    </nav>
</header>