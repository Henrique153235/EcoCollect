<?php

require_once __DIR__ . '/banco/crud.php';
require_once __DIR__ . '/banco/database.php';

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id'])) {
    $pontoColeta = read(database(), "PontosDeColeta", "id = {$_GET['id']}");
    $materiaisPonto = readAll(database(), "ViewMateriaisDoPonto", "idPonto = {$_GET['id']}");

}else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $novoPontoColeta = [
        'cep' => $_POST['cep'],
        'status' => $_POST['status'],
    ];
    update(database(), "PontosDeColeta", $novoPontoColeta, "id = {$_GET['id']}");
    
    print_r($_POST);
    delete(database(), "PontoMaterial", "idPonto = {$_GET['id']}");
    foreach($_POST['opcoes'] as $o => $chave){
        create(database(), "PontoMaterial", [ 'idPonto' => $_GET['id'], 'idMaterial' => $chave ]);
    }

     header("Location: teste_ponto.php");
    exit;
}else{
    header("Location: teste_ponto.php?erro=nao_selecionado");
   exit;

}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ponto</title>
</head>

<body>
    <form action="" method="post">
        <h1>Edição de Ponto de Coleta</h1>
        <label for="cep">Cep:</label>
        <input type="text" name="cep" id="cep" value="<?= $pontoColeta['cep'] ?>">
        <?php
        $materiais = readAll(database(), "Material");
        foreach ($materiais as $m): ?>
            <label for="<?= $m['tipo'] ?>">
                <input type="checkbox" id="<?= $m['tipo'] ?>" name="opcoes[<?= $m['tipo'] ?>]" value="<?= $m['id'] ?>" 
                <?php 
                if(in_array($m['id'], array_column($materiaisPonto, 'idMaterial'))){
                    echo "checked";
                }
                ?>
                >
                <?= $m['tipo'] ?>
            </label>
        <?php endforeach; ?>

        <select name="status" id="">
            
            <option value="disponivel"
            <?php if($pontoColeta['status'] == "disponivel") echo "selected"; ?>
            >disponível</option>
            <option value="indisponivel"
            <?php if($pontoColeta['status'] == "indisponivel") echo "selected"; ?>
            >Indisponível</option>
        </select>

        <button type="submit">Confirmar</button>
        <button type="button"><a href="./teste_ponto.php">Cancelar</a></button>
    </form>

</body>

</html>