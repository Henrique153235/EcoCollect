<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php");
    exit;
}

$idUsuarioLogado = (int) $_SESSION['usuario_id'];
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
                'idUsuario' => $idUsuarioLogado,
                'cep'       => $cep,
                'status'    => in_array($status, ['disponivel', 'indisponivel'], true) ? $status : 'disponivel',
            ];

            $idPonto = create($pdo, "PontosDeColeta", $novoPontoColeta);

            if (is_array($opcoes)) {
                foreach ($opcoes as $idMaterial) {
                    create($pdo, "PontoMaterial", [
                        'idPonto'    => (int) $idPonto,
                        'idMaterial' => (int) $idMaterial,
                    ]);
                }
            }

            $pdo->commit();

            $_SESSION['flash_sucesso'] = "Ponto de coleta cadastrado com sucesso!";
            header("Location: visualizacao_ponto.php");
            exit;

            header("Location: visualizacao_ponto.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = "Erro ao cadastrar ponto de coleta: " . $e->getMessage();
        }
    }
}

$materiais = readAll($pdo, "Material");
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect | Cadastro de Ponto</title>
    <link rel="stylesheet" href="../css/cadastro_ponto_coleta.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="icon" type="image/png" href="../img/img-logo.jpg">
</head>

<body>

    <?php require_once __DIR__ . "/header.php" ?>

    <main>
        <section class="caixa-cadastro">
            <h2>Cadastrar Ponto de Coleta</h2>

            <?php if (!empty($erro)): ?>
                <p class="msg-erro"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <form action="cadastro_ponto_coleta.php" method="post">
                <div class="form-option">
                    <label for="cep">CEP:</label>
                    <input type="text" name="cep" id="cep" maxlength="9" placeholder="00000-000" required>
                </div>

                <div class="form-option">
                    <label>Materiais:</label>
                    <div class="lista-materiais">
                        <?php foreach ($materiais as $m): ?>
                            <label for="<?= htmlspecialchars($m['tipo']) ?>">
                                <input type="checkbox" id="<?= htmlspecialchars($m['tipo']) ?>" name="opcoes[<?= htmlspecialchars($m['tipo']) ?>]" value="<?= (int) $m['id'] ?>">
                                <?= htmlspecialchars(ucfirst($m['tipo'])) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-option">
                    <label for="status">Status:</label>
                    <select name="status" id="status">
                        <option value="disponivel">Disponível</option>
                        <option value="indisponivel">Indisponível</option>
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