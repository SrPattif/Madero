<?php
include('../../libs/databaseConnection.php');

$query = "SELECT * FROM tipos_taxas WHERE refundable=1;";
$result = mysqli_query($mysqli, $query);

$rows = array();
while ($row = mysqli_fetch_array($result)) {
    array_push($rows, $row);
}

$inputId = uniqid();
?>

<div class="tax">
    <div class="tax-remove">
        <i class='bx bx-x'></i>
    </div>

    <div class="tax-type">
        <div class="select">
            <select id="select-month">
                <option value="reject">Tipo</option>
                <?php
                    foreach ($rows as $row) {
                ?>
                <option value="<?php echo($row['id']); ?>"><?php echo($row['description']); ?></option>
                <?php
                    }
                ?>
            </select>
            <div class="select_arrow">
            </div>
        </div>
    </div>

    <div class="tax-value">
        <input type="text" id="tax-value-<?php echo($inputId); ?>" value="R$ 0" oninput="formatarValor('tax-value-<?php echo($inputId); ?>')">
    </div>
</div>