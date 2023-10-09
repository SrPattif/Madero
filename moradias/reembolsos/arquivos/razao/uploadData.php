<?php
include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtém o conteúdo do CSV e o JSON do corpo da requisição
    $jsonData = $_POST["json"]; // Supondo que o JSON esteja sendo enviado como 'json'

    // Decodifica o JSON para um array associativo
    $columns = json_decode($jsonData, true);

    if (isset($_FILES["csv"]) && $_FILES["csv"]["error"] === UPLOAD_ERR_OK) {
        $csvData = file_get_contents($_FILES["csv"]["tmp_name"]);
    } else {
        header("Content-Type: application/json");
        echo json_encode(array("sucesso" => false, "descricao" => "Erro ao carregar o arquivo CSV."));
        http_response_code(400);
        exit();
    }

    // Remove as colunas não utilizadas do CSV
    $lines = explode("\n", $csvData);
    $csvDelimiter = detectDelimiter($csvData);

    // Criar uma array com os valores de cada linha
    $linesValues = [];
    foreach ($lines as $linha) {
        $valores = explode($csvDelimiter, $linha);
        $linesValues[] = $valores;
    }
    $header = explode(",", implode(",", $linesValues[0]));

    $filteredCsvData = array();
    foreach ($lines as $line) {
        $csvRow = str_getcsv($line, $csvDelimiter);
        $filteredRow = array();

        if(sizeof($csvRow) > 2) {
            //echo('<br>---');
            //echo(sizeof($csvRow));]
            //var_dump($csvRow[0]);
            foreach ($columns as $mysqlColumn => $csvColumn) {
                $index = array_search($csvColumn, $header);
                //echo ('<br> index ' . $csvColumn . ' - ' . $mysqlColumn . ' -> ' . $index);
                array_push($filteredRow, $csvRow[$index]);
            }

            array_push($filteredCsvData, $filteredRow);
        }
    }

    //var_dump(sizeof($filteredCsvData));


    $insertArray = array();
    try {
        $columnsString = implode(",", array_keys($columns));

        $indexFor = 0;
        foreach ($filteredCsvData as $row) {
            $formedArray = array();
            $index = 0;
            foreach ($row as $rowVal) {
                $colVal = array_keys($columns)[$index];
                $formedArray[$colVal] = $rowVal;
                $index++;
            }
            array_push($insertArray, $formedArray);
        }

        //var_dump(sizeof($insertArray));


        $databaseColumnsType = array();
        $queryColumns = "SHOW COLUMNS FROM razao;";
        $resultColumns = $mysqli->query($queryColumns);

        if ($resultColumns->num_rows > 0) {
            while ($row = $resultColumns->fetch_assoc()) {
                $columnType = $row['Type'];
                $columnField = $row['Field'];
                if(str_contains($columnType, "varchar")) {
                    $databaseColumnsType[$columnField] = "string";

                } else if(str_contains($columnType, "date")) {
                    $databaseColumnsType[$columnField] = "string-date";

                } else if(str_contains($columnType, "int")) {
                    $databaseColumnsType[$columnField] = "number";

                } else if(str_contains($columnType, "double")) {
                    $databaseColumnsType[$columnField] = "double";

                }
            }
        }

        //var_dump($databaseColumnsType);

        $query = "REPLACE INTO razao (";
        $query .= implode(",", array_keys($insertArray[0]));
        $query .= ") VALUES";

        $valuesAmount = 0;
        $rowCount = 0;
        foreach ($insertArray as $dataArray) {
            $rowCount++;
            if($rowCount == 1) continue;
            if($valuesAmount > 0) $query .= ',';
            $query .= " (";
            $amount = 0;
            foreach ($dataArray as $key => $value) {
                $value = mysqli_real_escape_string($mysqli, $value);

                if($amount > 0) $query .= ',';

                if(empty($value) || $value == '' || strlen($value) < 1) {
                    $value = 0;
                }

                if(str_starts_with($value, "NF")) {
                    $value = trim(str_replace("NF", "", $value));
                }

                if($databaseColumnsType[$key] == "number") {
                    if(is_numeric($value)) {
                        $query .= (int) trim($value);

                    } else {
                        $query .= (int) 0;
                    }

                } else if($databaseColumnsType[$key] == "string") {
                    $date = date_create_from_format("d/m/Y", $value);
                    if ($date !== false) {
                        $value = date_format($date, "Y-m-d");

                    }
                    $query .= "'" . trim($value) . "'";

                } else if($databaseColumnsType[$key] == "string-date") {
                    $date = date_create_from_format("d/m/Y", $value);
                    if ($date !== false) {
                        $value = "'" . date_format($date, "Y-m-d") . "'";
                    } else {
                        $value = "NULL";
                    }
                    $query .= trim($value);

                } else if($databaseColumnsType[$key] == "double") {
                    $tempVal = str_replace(".", "", $value);
                    $tempVal = str_replace(",", ".", $tempVal);

                    $isFloat = isStringFloat($tempVal);
                    if ($isFloat == false) {
                        $query .= "'" . (float) floatval(trim($tempVal)) . "'";

                    } else {
                        $query .= "'" . trim($tempVal) . "'";
                    }
                }
                $amount ++;
            }

            $query .= ")";
            $valuesAmount++;
        }

        /*

        $query .= " ON DUPLICATE KEY UPDATE";
            $ind = 0;
            foreach (array_keys($insertArray[0]) as $col) {
                if($ind == 0) {
                    $query .= " ";
                } else {
                    $query .= ", ";
                }
                $query .= $col . "=VALUES(" . $col . ")";
                $ind++;
            }
            
        */
        $query .= ";";

        $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $query);
        fclose($myfile);

        $resultColumns = $mysqli->query($query);
        if($resultColumns) {
            // Remover dados duplicados
            /*
            $resultDuplicated = $mysqli->query("DELETE FROM razao WHERE documento IN (SELECT documento FROM razao GROUP BY documento HAVING COUNT(*) > 1) AND id NOT IN (SELECT MAX(id) FROM razao GROUP BY documento HAVING COUNT(*) > 1);");
        
            if($resultDuplicated) {
                header("Content-Type: application/json");
                echo json_encode(array("sucesso" => true, "descricao" => "Banco de dados atualizado."));
                http_response_code(200);
                exit();

            } else {
                header("Content-Type: application/json");
                echo json_encode(array("sucesso" => false, "descricao" => "Erro desconhecido na execução da query de remoção de duplicatas."));
                http_response_code(500);
                exit();
            }*/

            header("Content-Type: application/json");
            echo json_encode(array("sucesso" => true, "descricao" => "Banco de dados atualizado."));
            http_response_code(200);
            exit();

        } else {
            header("Content-Type: application/json");
            echo json_encode(array("sucesso" => false, "descricao" => "Erro desconhecido na execução da query de inserção de dados."));
            http_response_code(500);
            exit();
        }
    } catch (Exception $e) {
        echo "Erro na execução da query: " . $e->getMessage();
        echo json_encode(array("sucesso" => false, "descricao" => "Erro desconhecido na execução da query.", "error_desc" => $e->getMessage()));
        http_response_code(500);
        exit();
    }
}

function isStringFloat($string) {
    if(is_numeric($string)) {
        $val = $string+0;

        return is_float($val);
    } 
      
    return false;
}

function detectDelimiter($csvData) {
    $delimiters = array(',', ';', '\t', ':'); // Adicione outros possíveis delimitadores aqui
    
    $delimiterCount = array();
    
    foreach ($delimiters as $delimiter) {
        $lines = explode("\n", $csvData);
        $count = 0;
        
        foreach ($lines as $line) {
            $fields = str_getcsv($line, $delimiter);
            
            if (count($fields) > 1) {
                $count++;
            }
        }
        
        $delimiterCount[$delimiter] = $count;
    }
    
    // Encontre o delimitador com maior contagem
    $maxCount = max($delimiterCount);
    $bestDelimiters = array_keys($delimiterCount, $maxCount);
    
    // Retorne o primeiro delimitador com a maior contagem
    return $bestDelimiters[0];
}

?>