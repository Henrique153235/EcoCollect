<?php

require_once __DIR__ . "/banco/crud.php";
require_once __DIR__ . "/banco/database.php";

$pontoColeta = readAll(database(), "ViewPontosDeColeta");

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TESTE - PONTOS DE COLETA</title>
</head>
<body>
    <a href="cadastro_ponto_coleta.php">Cadastrar um ponto</a>
    <br>
    <br>

    <table border="1">
        <thead>
            <tr>
                <td>ID</td>
                <td>CEP</td>
                <td>Status</td>
                <td>Materiais</td>
                <td>Ações</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pontoColeta as $p): ?>
            <tr>
                <td> <?= $p['id'] ?> </td>
                <td> <?= $p['cep'] ?> </td>
                <td> <?= $p['status'] ?> </td>
                <td> <?= $p['materiais'] ?> </td>
                <td>
                    <a href="edicao_ponto_coleta.php?id=<?= $p['id'] ?>">Editar</a>
                    <a href="remocao_ponto_coleta.php?id=<?= $p['id'] ?>">Remover</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>