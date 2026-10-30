<?php

require_once __DIR__ . '/banco/crud.php';
require_once __DIR__ . '/banco/database.php';

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id'])) {
    $pontoColeta = read(database(), "PontosDeColeta", "id = {$_GET['id']}");
}

elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
    print_r($_POST);
    $novoPontoColeta = [
        'cep' => $_POST['cep'],
        'status' => $_POST['status']
    ];
    $idPonto = create(database(), "PontosDeColeta", $novoPontoColeta);
    foreach ($_POST['opcoes'] as $o => $chave) {
        create(database(), "PontoMaterial", ['idPonto' => $idPonto, 'idMaterial' => $chave]);
    }
    header("Location: teste_ponto.php");
    exit;
}

else {
    header("Location: teste_ponto.php?erro=nao_selecionado");
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criação de Ponto</title>
</head>

<body>


    <h1>ID: <?= $pontoColeta['id'] ?></h1>
    <ul>
        <li>CEP: <?= $pontoColeta['cep'] ?></li>
        <li>Status: <?= $pontoColeta['status'] ?></li>
        <?php foreach($pontoColeta['materiais'] as $m) ?>
        <li><?= $m ?></li>
    </ul>
    <br>
    <form action="" method="post">
        <button type="submit">Confirmar Deleção</button>
        <button type="button"><a href="/index.php">Cancelar</a></button>
    </form>
    </form>

</body>

</html>