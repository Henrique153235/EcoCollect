<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php");
    exit;
}

$idUsuarioLogado = (int) $_SESSION['usuario_id'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    $_SESSION['flash_erro'] = "Ponto de coleta inválido.";
    header("Location: visualizacao_ponto.php");
    exit;
}

$pontoColeta = read($pdo, "PontosDeColeta", "id = ?", [$id]);

if (!$pontoColeta) {
    $_SESSION['flash_erro'] = "Ponto de coleta não encontrado.";
    header("Location: visualizacao_ponto.php");
    exit;
}

if ((int) ($pontoColeta['idUsuario'] ?? 0) !== $idUsuarioLogado) {
    $_SESSION['flash_erro'] = "Você não tem permissão para editar este ponto de coleta.";
    header("Location: visualizacao_ponto.php");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cep = trim($_POST['cep'] ?? '');
    $status = $_POST['status'] ?? 'disponivel';
    $opcoes = $_POST['opcoes'] ?? [];

    if ($cep == '') {
        $erro = "Informe o CEP do ponto de coleta.";
    } else {
        try {
            $pdo->beginTransaction();

            $novoPontoColeta = [
                'cep'    => $cep,
                'status' => in_array($status, ['disponivel', 'indisponivel'], true) ? $status : 'disponivel',
            ];

            update($pdo, "PontosDeColeta", $novoPontoColeta, "id = ? AND idUsuario = ?", [$id, $idUsuarioLogado]);
            delete($pdo, "PontoMaterial", "idPonto = ?", [$id]);

            if (is_array($opcoes)) {
                foreach ($opcoes as $idMaterial) {
                    create($pdo, "PontoMaterial", [
                        'idPonto'    => $id,
                        'idMaterial' => (int) $idMaterial,
                    ]);
                }
            }

            $pdo->commit();

            $_SESSION['flash_sucesso'] = "Ponto de coleta atualizado com sucesso!";
            header("Location: visualizacao_ponto.php");
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $erro = "Erro ao atualizar ponto de coleta: " . $e->getMessage();
        }
    }
}

$materiaisPonto = readAll($pdo, "ViewMateriaisDoPonto", "idPonto = ?", [$id]);
$materiais = readAll($pdo, "Material");
$idsMateriaisSelecionados = array_column($materiaisPonto, 'idMaterial');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect | Editar Ponto de Coleta</title>
    <link rel="stylesheet" href="../css/edicao_ponto_coleta.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="icon" type="image/png" href="../img/img-logo.jpg">
</head>

<body>

    <?php require_once __DIR__ . "/header.php" ?>

    <main>
        <section class="caixa-edicao">
            <h2>Editar Ponto de Coleta</h2>

            <?php if (!empty($erro)): ?>
                <p class="msg-erro"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <form action="edicao_ponto_coleta.php?id=<?= (int) $id ?>" method="post">
                <div class="form-option">
                    <label for="cep">CEP:</label>
                    <input type="text" name="cep" id="cep" maxlength="9" value="<?= htmlspecialchars($pontoColeta['cep'] ?? '') ?>" required>
                </div>

                <div class="form-option">
                    <label>Materiais:</label>
                    <div class="lista-materiais">
                        <?php foreach ($materiais as $m): ?>
                            <label for="<?= htmlspecialchars($m['tipo']) ?>">
                                <input
                                    type="checkbox"
                                    id="<?= htmlspecialchars($m['tipo']) ?>"
                                    name="opcoes[<?= htmlspecialchars($m['tipo']) ?>]"
                                    value="<?= (int) $m['id'] ?>"
                                    <?= in_array($m['id'], $idsMateriaisSelecionados) ? 'checked' : '' ?>
                                >
                                <?= htmlspecialchars(ucfirst($m['tipo'])) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-option">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="disponivel" <?= ($pontoColeta['status'] ?? '') == 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                        <option value="indisponivel" <?= ($pontoColeta['status'] ?? '') == 'indisponivel' ? 'selected' : '' ?>>Indisponível</option>
                    </select>
                </div>

                <div class="form-btns">
                    <button type="submit" class="btn-confirmar">Confirmar</button>
                    <a href="visualizacao_ponto.php" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </section>
    </main>

    <?php require_once __DIR__ . "/footer.php" ?>

</body>

</html>