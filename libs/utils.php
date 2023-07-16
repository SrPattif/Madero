<?php
include('databaseConnection.php');

function pickRandomAddress($month, $year) {
    global $mysqli;

    $month = mysqli_real_escape_string($mysqli, $month);
    $year = mysqli_real_escape_string($mysqli, $year);

    $query = "SELECT DISTINCT a.* FROM alojamentos a LEFT JOIN alojamentos_valores_reembolso ar ON a.id = ar.id_alojamento AND ar.ano = {$year} AND ar.mes = {$month} INNER JOIN boletos b ON b.id_alojamento = a.id AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year} WHERE ar.id_alojamento IS NULL;";
    $result = mysqli_query($mysqli, $query);
    $rows = array();
    while($row = mysqli_fetch_array($result)){
        array_push($rows, $row);
    }

    if (!empty($rows)) {
        return $rows[0]['id'];
    } else {
        return -1;
    }
}

function hasActiveMeditions($addressId, $month, $year) {
    global $mysqli;
    $addressId = mysqli_real_escape_string($mysqli, $addressId);
    $month = mysqli_real_escape_string($mysqli, $month);
    $year = mysqli_real_escape_string($mysqli, $year);

    $query = "SELECT * FROM alojamentos_valores_reembolso WHERE id_alojamento='{$addressId}' AND ano='{$year}' AND mes='{$month}';";
    $result = mysqli_query($mysqli, $query);
    $row = mysqli_num_rows($result);

    if ($row > 0) {
        return true;
    } else {
        return false;
    }
}

function getAddressData($addressId) {
    global $mysqli;
    $addressId = mysqli_real_escape_string($mysqli, $addressId);

    $query = "SELECT * FROM alojamentos WHERE id='{$addressId}';";
    $result = mysqli_query($mysqli, $query);
    $row = mysqli_num_rows($result);

    if ($row == 1) {
        $addressData = mysqli_fetch_assoc($result);
        return $addressData;
    } else {
        return '-';
    }
}

function getAddresMeditions($addressId, $month, $year) {
    global $mysqli;
    $addressId = mysqli_real_escape_string($mysqli, $addressId);
    $month = mysqli_real_escape_string($mysqli, $month);
    $year = mysqli_real_escape_string($mysqli, $year);

    $query = "SELECT ar.ano,ar.mes,ar.id_alojamento,ar.valor_taxa,ar.reembolsado,tt.description,tt.refundable FROM alojamentos_valores_reembolso ar INNER JOIN tipos_taxas tt ON ar.id_taxa=tt.id WHERE id_alojamento={$addressId} AND ar.ano='{$year}' AND ar.mes='{$month}';";
    $result = mysqli_query($mysqli, $query);
    $rows = array();
    while($row = mysqli_fetch_array($result)){
        array_push($rows, $row);
    }

    return $rows;
}

?>