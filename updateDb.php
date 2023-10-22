<?php

include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

$query = "SELECT * FROM comprovantes;";
$result = mysqli_query($mysqli, $query);
$rows = array();
while($row = mysqli_fetch_array($result)){
    array_push($rows, $row);
}

foreach($rows as $row) {
    $hash = hash_file('sha256', $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $row['nome_interno']);
    echo($row['nome_interno']);
    echo(' ->> ');
    echo($hash);
    

    $query = "UPDATE comprovantes SET hash='{$hash}' WHERE `id`={$row['id']};";
    $resultUpdate = mysqli_query($mysqli, $query);
            
    if($resultUpdate) {
        echo('  ----  update ok');
    }

    echo('<br />');

}

?>