<?php

require_once __DIR__ . "/banco/crud.php";
require_once __DIR__ . "/banco/database.php";

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id'])) {
    $pontosColeta = read(database(), "ViewPontosDeColeta", "id = {$_GET['id']}");
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    delete(database(), "PontosDeColeta", "id = {$_GET['id']}");
    header("Location: visualizacao_ponto.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir um Ponto</title>
</head>

<body>
    <form action="" method="post">
        <h1>Excluir Ponto de Coleta</h1>
        <ul>
            <li>ID: <?= $pontosColeta['id'] ?></li>
            <li>CEP: <?= $pontosColeta['cep'] ?></h1>
            <li>Status: <?= $pontosColeta['status'] ?></li>
            <li>Materiais: <?= $pontosColeta['materiais'] ?></li>
        </ul>
        <button type="submit">Confirmar Exclusão</button>
        <button type="button"><a href="./visualizacao_ponto.php">Cancelar</a></button>
    </form>

</body>

</html>