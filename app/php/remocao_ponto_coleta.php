<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: visualizacao_ponto.php?erro=id_invalido");
    exit;
}

$pontosColeta = read($pdo, "ViewPontosDeColeta", "id = ?", [$id]);

if (!$pontosColeta) {
    header("Location: visualizacao_ponto.php?erro=nao_encontrado");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        delete($pdo, "PontosDeColeta", "id = ?", [$id]);
        header("Location: visualizacao_ponto.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Não foi possível excluir este ponto porque já existem coletas registradas nele. Caso não esteja mais ativo, altere o status para Indisponível na edição.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect | Excluir Ponto de Coleta</title>
    <link rel="stylesheet" href="../css/remocao_ponto_coleta.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="icon" type="image/png" href="../img/img-logo.jpg">
</head>

<body>

    <?php require_once __DIR__ . "/header.php" ?>

    <main>
        <section class="caixa-remocao">
            <h2>Excluir Ponto de Coleta</h2>

            <?php if (!empty($erro)): ?>
                <p class="msg-erro"><?= htmlspecialchars($erro) ?></p>
            <?php else: ?>
                <p class="mensagem-alerta">Tem certeza que deseja remover este ponto de coleta?</p>
            <?php endif; ?>

            <form action="remocao_ponto_coleta.php?id=<?= (int) $id ?>" method="post">
                <ul class="lista-detalhes">
                    <li><strong>ID:</strong> #<?= htmlspecialchars((string) ($pontosColeta['id'] ?? '')) ?></li>
                    <li>
                        <?php
                        $cep = $pontosColeta['cep'] ?? '';
                        $cep_formatado = (strlen($cep) == 8) ? substr($cep, 0, 5) . '-' . substr($cep, 5) : $cep;
                        ?>
                        <strong>CEP:</strong> <?= htmlspecialchars($cep_formatado) ?>
                    </li>
                    <li><strong>Status:</strong> <?= htmlspecialchars(ucfirst($pontosColeta['status'] ?? '')) ?></li>
                    <li><strong>Materiais:</strong> <?= htmlspecialchars($pontosColeta['materiais'] ?? '') ?></li>
                </ul>

                <div class="form-btns">
                    <button type="submit" class="btn-confirmar-exclusao">Confirmar Exclusão</button>
                    <a href="visualizacao_ponto.php" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

<?php require_once __DIR__ . "/footer.php" ?>

</body>

</html>