<?php
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['USER_ID'])) {
    http_response_code(401);
    exit();
}

include($_SERVER['DOCUMENT_ROOT'] . '/libs/databaseConnection.php');

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

$listaMeses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
$nomeMes = $listaMeses[$month - 1];

if(!isset($_GET['id_moradia'])) {
    http_response_code(401);
    exit();
}

$idMoradia = mysqli_real_escape_string($mysqli, $_GET['id_moradia']);

$queryAlojamento = "SELECT a.endereco, a.id, tt.description AS nome_taxa, avr.valor_taxa, b.nome_interno AS arquivo_boleto, b.arquivo_comprovante FROM alojamentos_valores_reembolso avr INNER JOIN tipos_taxas tt ON avr.id_taxa=tt.id LEFT JOIN alojamentos a ON a.id=avr.id_alojamento LEFT JOIN boletos b ON b.id_alojamento=a.id AND MONTH(b.data_vencimento)={$month} AND YEAR(b.data_vencimento)={$year} WHERE a.id={$idMoradia} AND tt.refundable=1 AND avr.mes={$month} AND avr.ano={$year};";
$rowsAlojamento = array();
$resultAlojamento = mysqli_query($mysqli, $queryAlojamento);
if(mysqli_num_rows($resultAlojamento) < 1) {
    http_response_code(400);
    exit();
}
$rowsAlojamento = array();
while($row = mysqli_fetch_array($resultAlojamento)){
    array_push($rowsAlojamento, $row);
}
$dadosAlojamento = $rowsAlojamento[0];
$idMoradia = $dadosAlojamento['id'];

$queryContatos = "SELECT * FROM contatos_reembolso WHERE id_alojamento={$idMoradia};";
$resultContatos = mysqli_query($mysqli, $queryContatos);
$rowsContatos = array();
while($row = mysqli_fetch_array($resultContatos)){
    array_push($rowsContatos, $row);
}

if(sizeof($rowsContatos) <= 0) {
    http_response_code(401);
    exit();
}

if(empty($dadosAlojamento['arquivo_boleto']) || empty($dadosAlojamento['arquivo_comprovante'])) {
    http_response_code(401);
    exit();
}

$stringDestinatarios = '';

foreach($rowsContatos as $contato) {
    if($stringDestinatarios != '') $stringDestinatarios .= ', ';
    $stringDestinatarios .= '"' . $contato['email_reembolso'] . '" <' . $contato['email_reembolso'] . '>';
}

$b64boleto = '';
$b64comprovante = '';

if(!empty($dadosAlojamento['arquivo_boleto'])) {
    $b64boleto = chunk_split(base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $dadosAlojamento['arquivo_boleto'])));
}
if(!empty($dadosAlojamento['arquivo_comprovante'])) {
    $b64comprovante = chunk_split(base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $dadosAlojamento['arquivo_comprovante'])));
}

$totalReembolsavel = 0.0;

?>
To: <?php echo($stringDestinatarios); ?> 
Cc: "controladoria.aluguel@grupomadero.com.br" <controladoria.aluguel@grupomadero.com.br>
Subject: <?php echo($nomeMes); ?> - Solicitação de Reembolso - <?php echo($dadosAlojamento['endereco']); ?> 
X-Unsent: 1
Content-Type: multipart/mixed; boundary="A"

--A
Content-Type: text/html; charset=UTF-8;
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline

<html>
<body>
<div id="mensagem">
        <p>Olá.</p>
        <p>Nós alugamos o imóvel <b><?php echo($dadosAlojamento['endereco']); ?></b>, onde o pagamento do boleto de condomínio é de nossa responsabilidade como inquilinos, mas identificamos cobranças no boleto de condomínio com vencimento em <b><?php echo(strtolower($nomeMes)); ?></b> que são de responsabilidade do proprietário do imóvel.</p>
        <span>As taxas extras são as seguintes:</span>
        <table>
            <tr>
                <th>Taxa Extra</th>
                <th>Valor</th>
            </tr>
            <?php
            foreach($rowsAlojamento as $row) {
                $totalReembolsavel += $row['valor_taxa'];
            ?>
            <tr>
                <td><?php echo($row['nome_taxa']); ?></td>
                <td style="width: 30%;">R$ <?php echo(number_format($row['valor_taxa'], 2, ',', '.')); ?></td>
            </tr>
            <?php
            }
            ?>
        </table>
        <p>Com isso, solicitamos o reembolso de <b>R$ <?php echo(number_format($totalReembolsavel, 2, ',', '.')); ?></b>, referente a somatória de taxas extras, no próximo boleto de aluguel. Se o aluguel for pago através de depósito bancário, esse valor <b>será descontado do próximo pagamento</b> e este serve como informativo.
        <br>
        Você também pode realizar o reembolso da taxa através de um depósito bancário. Para isso, precisamos que você solicite os dados para depósito e nos informe a data em que o valor será creditado.</p>
        <p>O boleto de condomínio e seu comprovante de pagamento se encontram em anexo.</p>
        <p>Fico no aguardo de um retorno.</p>
    </div>
</body>
</html>
<?php
    if(!empty($b64boleto)) {
?>

--A
Content-Type: application/octet-stream; name="Boleto de Condomínio - <?php echo($dadosAlojamento['endereco']); ?>.pdf"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="Boleto de Condomínio - <?php echo($dadosAlojamento['endereco']); ?>.pdf"

<?php echo($b64boleto);

    }
?>
<?php
    if(!empty($b64comprovante)) {
?>

--A
Content-Type: application/octet-stream; name="Comprovante de Pagamento - <?php echo($dadosAlojamento['endereco']); ?>.pdf"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="Comprovante de Pagamento - <?php echo($dadosAlojamento['endereco']); ?>.pdf"

<?php echo($b64comprovante);

    }
?>