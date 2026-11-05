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

<!-- TESTE -->

    <section class="dashboard-container">
        <div class="dashboard-header">
            <h2>Pontos de Coleta Cadastrados</h2>
            <a href="cadastro_ponto_coleta.php" class="btn-cadastrar">+ Cadastrar Novo Ponto</a>
        </div>

        <div class="grid-cards">
            <?php foreach ($pontoColeta as $p): ?>
                <article class="card-ponto">
                    <div class="card-header">
                        <span class="card-id">Ponto #<?= htmlspecialchars($p['id']) ?></span>
                        <span class="card-status status-<?= strtolower(htmlspecialchars($p['status'])) ?>">
                            <?= htmlspecialchars($p['status']) ?>
                        </span>
                    </div>

                    <div class="card-body">
                        <p class="card-info">
                            <strong>CEP:</strong> <?= htmlspecialchars($p['cep']) ?>
                        </p>
                        <p class="card-info">
                            <strong>Materiais Aceitos:</strong>
                            <span class="materiais-lista"><?= htmlspecialchars($p['materiais']) ?></span>
                        </p>
                    </div>

                    <div class="card-actions">
                        <a href="edicao_ponto_coleta.php?id=<?= $p['id'] ?>" class="btn-acao btn-editar">Editar</a>
                        <a href="remocao_ponto_coleta.php?id=<?= $p['id'] ?>" class="btn-acao btn-remover">Remover</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</body>

</html>