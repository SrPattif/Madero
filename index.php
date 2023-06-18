<?php
    include('./libs/databaseConnection.php');
    include($_SERVER['DOCUMENT_ROOT'] . '/login_checker.php');

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

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2F2Z7S0VR0"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    </script>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moradias Grupo Madero | Gestão de Reembolsos</title>

    <!-- Estilos -->
    <link rel="stylesheet" href="./defaultStyle.css" />
    <link rel="stylesheet" href="./index.css" />

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <?php
    require($_SERVER['DOCUMENT_ROOT'] . '/header.php');
    ?>

    <main>
        <div class="page-content">
            <div class="double-cards">
                <div class="card">
                    <h3>Ranking de Moradias</h3>

                    <table class="ranking-table">
                        <tr>
                            <th>#</th>
                            <th>Endereço</th>
                            <th>Valor Condomínio</th>
                            <th>Valor Reembolsável</th>
                        </tr>
                        <?php
                            $query = "SELECT ar.ano,ar.mes,ar.id_alojamento,ar.valor_taxa,ar.status,tt.description,tt.refundable,a.endereco,a.id FROM alojamentos_valores_reembolso ar INNER JOIN tipos_taxas tt ON ar.id_taxa=tt.id INNER JOIN alojamentos a ON ar.id_alojamento=a.id WHERE ano={$year} AND mes={$month} ORDER BY CASE WHEN refundable = 1 THEN valor_taxa ELSE NULL END DESC;";
                            $result = mysqli_query($mysqli, $query);
                            $rows = array();
                            while($row = mysqli_fetch_array($result)){
                                array_push($rows, $row);
                            }

                            $addresses = array();
                            $refundableValue = array();
                            $condominiumValue = array();

                            $index = 1;
                            foreach($rows as $row) {
                                $addressId = $row['id'];
                                $address = $row['endereco'];

                                $addresses[$addressId] = $address;

                                if(boolval($row['refundable'])) {
                                    if(isset($refundableValue[$addressId])) {
                                        $refundableValue[$addressId] += $row['valor_taxa'];
                                    } else {
                                        $refundableValue[$addressId] = $row['valor_taxa'];
                                    }
                                } else {
                                    if(isset($condominiumValue[$addressId])) {
                                        $condominiumValue[$addressId] += $row['valor_taxa'];
                                    } else {
                                        $condominiumValue[$addressId] = $row['valor_taxa'];
                                    }
                                }
                            }

                            foreach ($addresses as $addressId => $address) {
                                $cond = 0.0;
                                if(isset($condominiumValue[$addressId])) {
                                    $cond = $condominiumValue[$addressId];
                                }

                                $refund = 0.0;
                                if(isset($refundableValue[$addressId])) {
                                    $refund = $refundableValue[$addressId];
                                }
                        ?>

                        <tr>
                            <td><?php echo($index); ?></td>
                            <td><?php echo($address); ?></td>
                            <td>R$ <?php echo(number_format($cond, 2, ",", ".")); ?></td>
                            <td>R$ <?php echo(number_format($refund, 2, ",", ".")); ?></td>
                        </tr>

                        <?php   
                                if($index >= 5) {
                                    break;
                                } 
                                $index++;
                            }
                        ?>
                    </table>
                </div>
                <div class="card">
                    <h3>Ranking de Taxas Extras</h3>

                    <div class="custom-scroll">
                        <table class="ranking-table">
                            <tr>
                                <th>#</th>
                                <th>Descrição</th>
                                <th>Valor Total</th>
                            </tr>

                            <?php
                            $query = "SELECT tt.id, tt.description, SUM(avr.valor_taxa) AS maior_valor FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable=1 AND avr.mes={$month} AND avr.ano={$year} GROUP BY tt.id, tt.description ORDER BY maior_valor DESC LIMIT 5";
                            $result = mysqli_query($mysqli, $query);
                            $rows = array();
                            while($row = mysqli_fetch_array($result)){
                                array_push($rows, $row);
                            }

                            $index = 1;
                            foreach($rows as $row) {
                        ?>

                            <tr>
                                <td><?php echo($index); ?></td>
                                <td><?php echo($row['description']); ?></td>
                                <td>R$ <?php echo(number_format($row['maior_valor'], 2, ",", ".")); ?></td>
                            </tr>
                            <?php 
                                $index++;
                            }
                        ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="double-cards">
                <div class="card">
                    <div class="card-header">
                        <?php
                            $query = "SELECT SUM(avr.valor_taxa) AS soma_valores FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 1 AND avr.mes={$month} AND avr.ano={$year};";
                            $result = mysqli_query($mysqli, $query);
                            $row = mysqli_num_rows($result);

                            $refundableValue = 0.0;
                            if ($row == 1) {
                                $refundableObject = mysqli_fetch_assoc($result);
                                $refundableValue = $refundableObject['soma_valores'];
                            }
                        ?>
                        <h1>R$ <?php echo(number_format($refundableValue, 2, ",", ".")); ?></h1>
                        <span>Total Reembolsável</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <?php
                            $query = "SELECT SUM(avr.valor_taxa) AS soma_valores FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 1 AND avr.mes={$month} AND avr.ano={$year} AND avr.status='refunded';";
                            $result = mysqli_query($mysqli, $query);
                            $row = mysqli_num_rows($result);

                            $refundedValue = 0.0;
                            if ($row == 1) {
                                $refundedObject = mysqli_fetch_assoc($result);
                                $refundedValue = $refundedObject['soma_valores'];
                            }
                        ?>
                        <h1>R$ <?php echo(number_format($refundedValue, 2, ",", ".")); ?></h1>
                        <span>Total Reembolsado</span>
                    </div>
                </div>
            </div>

            <div class="double-cards">
                <div class="card">
                    <div class="card-header">
                        <?php
                            $percentage = 0.0;
                            if($refundableValue > 0) {
                                $percentage = ($refundedValue / $refundableValue) * 100;
                            }
                        ?>
                        <h1><?php echo(number_format($percentage, 1, ",", ".")); ?>%</h1>
                        <span>Índice de Valores Reembolsados</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <?php
                            $query = "SELECT SUM(avr.valor_taxa) AS soma_valores FROM tipos_taxas tt JOIN alojamentos_valores_reembolso avr ON tt.id = avr.id_taxa WHERE tt.refundable = 0 AND avr.mes={$month} AND avr.ano={$year};";
                            $result = mysqli_query($mysqli, $query);
                            $row = mysqli_num_rows($result);

                            $refundableValue = 0.0;
                            if ($row == 1) {
                                $refundableObject = mysqli_fetch_assoc($result);
                                $refundableValue = $refundableObject['soma_valores'];
                            }
                        ?>
                        <h1>R$ <?php echo(number_format($refundableValue, 2, ",", ".")); ?></h1>
                        <span>Total em Condomínios</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php
    //require('footer.php');
    ?>

    <script src="mobile-navbar.js"></script>
    <script src="/libs/tatatoast/dist/tata.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
</body>

</html>