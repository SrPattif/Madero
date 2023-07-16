<?php

/*
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    http_response_code(403);
    exit();
}

*/

include('../../libs/databaseConnection.php');

if(!isset($_POST['addressId']) || !isset($_POST['year']) || !isset($_POST['month']) || !isset($_POST['taxes']) || !is_array($_POST['taxes'])) {
    http_response_code(403);
    exit();
}

$addressId = mysqli_real_escape_string($mysqli, $_POST['addressId']);
$month = mysqli_real_escape_string($mysqli, $_POST['month']);
$year = mysqli_real_escape_string($mysqli, $_POST['year']);

$taxesArray = $_POST['taxes'];

$query = "INSERT INTO alojamentos_valores_reembolso (`ano`, `mes`, `id_alojamento`, `id_taxa`, `valor_taxa`) VALUES ";

$taxIndex = 0;
foreach ($taxesArray as $tax) {
    $taxId = mysqli_real_escape_string($mysqli, $tax['id']);
    $taxValue = mysqli_real_escape_string($mysqli, $tax['value']);

    $taxId = (int) $taxId;
    $taxValue = floatval($taxValue);

    if($taxIndex != 0) {
        $query .= ", ";
    }
    $query .= "('{$year}', '{$month}', '{$addressId}', '{$taxId}', '{$taxValue}')";
    $taxIndex++;
}
$query .= ";";

$deleteQuery = "DELETE FROM alojamentos_valores_reembolso WHERE id_alojamento={$addressId} AND mes={$month} AND ano={$year};";
error_log($deleteQuery);
mysqli_query($mysqli, $deleteQuery);

$result = mysqli_query($mysqli, $query);

if ($result) {
    http_response_code(200);
    exit();

} else {
    http_response_code(500);
    exit();
}

?>