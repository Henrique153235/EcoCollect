<?php

require_once __DIR__ . '/banco/crud.php';
require_once __DIR__ . '/banco/database.php';

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id'])) {
    $pontoColeta = read(database(), "PontosDeColeta", "id = {$_GET['id']}");
    $materiaisPonto = readAll(database(), "ViewMateriaisDoPonto", "idPonto = {$_GET['id']}");
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $novoPontoColeta = [
        'cep' => $_POST['cep'],
        'status' => $_POST['status'],
    ];
    update(database(), "PontosDeColeta", $novoPontoColeta, "id = {$_GET['id']}");

    print_r($_POST);
    delete(database(), "PontoMaterial", "idPonto = {$_GET['id']}");
    foreach ($_POST['opcoes'] as $o => $chave) {
        create(database(), "PontoMaterial", ['idPonto' => $_GET['id'], 'idMaterial' => $chave]);
    }

    header("Location: visualizacao_ponto.php");
    exit;
} else {
    header("Location: teste_ponto.php?erro=nao_selecionado");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ponto de Coleta | EcoCollect</title>
    <link rel="stylesheet" href="edicao_ponto_coleta.css">
</head>

<body>

    <header>
        <div class="nome-projeto">
            <h1>EcoCollect</h1>
            <h3>by Ambientify</h3>
        </div>

        <nav>
            <a href="index.html">Início</a>
            <a href="dashboard.html">Dashboard</a>

            <details name="menu-nav">
                <summary>Cadastro</summary>
                <div class="sub-menu">
                    <a href="">Usuário</a>
                    <a href="">Ponto de Coleta</a>
                    <a href="">Campanha</a>
                </div>
            </details>

            <details name="menu-nav">
                <summary>Coletas</summary>
                <div class="sub-menu menu-direita">
                    <a href="">Orientações de Descarte</a>
                    <a href="">Campanhas de Descarte</a>
                    <a href="">Pontos de Coleta Próximos</a>
                </div>
            </details>
        </nav>
    </header>

    <main>
        <h2 class="box-titulo">Editar Ponto de Coleta</h2>
        <form action="" method="post">
            <div class="form-option">
                <label for="cep">Cep:</label>
                <input type="text" name="cep" id="cep" value="<?= $pontoColeta['cep'] ?>">
            </div>
            <div class="form-option">
                <label for="materiais">Materiais:</label>
                <?php
                $materiais = readAll(database(), "Material");
                foreach ($materiais as $m): ?>
                    <label for="<?= $m['tipo'] ?>">
                        <input type="checkbox" id="<?= $m['tipo'] ?>" name="opcoes[<?= $m['tipo'] ?>]" value="<?= $m['id'] ?>"
                            <?php
                            if (in_array($m['id'], array_column($materiaisPonto, 'idMaterial'))) {
                                echo "checked";
                            }
                            ?>>
                        <?= $m['tipo'] ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="form-option">
                <label for="status">Status:</label>
                <select name="status" id="">
                    <option value="disponivel"
                        <?php if ($pontoColeta['status'] == "disponivel") echo "selected"; ?>>disponível</option>
                    <option value="indisponivel"
                        <?php if ($pontoColeta['status'] == "indisponivel") echo "selected"; ?>>Indisponível</option>
                </select>
            </div>

            <div class="form-btns">
                <button type="submit" class="btn-acao btn-confirmar">Confirmar</button>
                <a href="./visualizacao_ponto.php" class="btn-acao btn-cancelar">Cancelar</a>
            </div>
        </form>
    </main>

</body>

</html>