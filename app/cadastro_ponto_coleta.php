<?php

require_once __DIR__ . '/banco/crud.php';
require_once __DIR__ . '/banco/database.php';

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id'])) {
    $pontoColeta = read(database(), "PontosDeColeta", "id = {$_GET['id']}");
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    print_r($_POST);
    $novoPontoColeta = [
        'cep' => $_POST['cep'],
        'status' => $_POST['status']
    ];
    $idPonto = create(database(), "PontosDeColeta", $novoPontoColeta);
    foreach($_POST['opcoes'] as $o => $chave){
        create(database(), "PontoMaterial", [ 'idPonto' => $idPonto, 'idMaterial' => $chave ]);
    }
    header("Location: teste_ponto.php");
    exit;
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

    <form action="" method="post">
        <h1>Cadastro de Ponto de Coleta</h1>
        <label for="cep">Cep:</label>
        <input type="text" name="cep" id="cep">
        <?php
        $materiais = readAll(database(), "Material");
        foreach ($materiais as $m): ?>
            <label for="<?= $m['tipo'] ?>">
                <input type="checkbox" id="<?= $m['tipo'] ?>" name="opcoes[<?= $m['tipo'] ?>]" value="<?= $m['id'] ?>">
                <?= $m['tipo'] ?>
            </label>
        <?php endforeach; ?>

        <select name="status" id="">
            <option value="disponivel">disponível</option>
            <option value="indisponivel">Indisponível</option>
        </select>
       
        <button type="submit">Confirmar</button>
        <button type="reset">Limpar</button>
        <button type="button"><a href="./teste_ponto.php">Cancelar</a></button>
    </form>

</body>

</html>