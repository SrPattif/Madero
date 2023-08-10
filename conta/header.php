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
        </div>
        <div class="mobile-menu">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>

        <ul class="nav-list">
            <li><a href="/" class="special-btn"><i class='bx bxs-package'></i> SELECIONAR MÓDULO</a></li>
        </ul>
    </nav>
</header>