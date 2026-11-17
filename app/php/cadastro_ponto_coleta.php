<?php

require_once __DIR__ . '/init.php';

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
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criação de Ponto | EcoCollect</title>
    <link rel="stylesheet" href="../css/cadastro_ponto_coleta.css">
</head>

<body>

    <header>
        <div class="nome-projeto">
            <h1>EcoCollect</h1>
            <h3>by Ambientify</h3>
        </div>

        <nav>
            <a href="index.php">Início</a>
            <a href="dashboard.php">Dashboard</a>

            <details name="menu-nav">
                <summary>Cadastro</summary>
                <div class="sub-menu">
                    <a href="cadastro_usuario.php">Usuário</a>
                    <a href="cadastro_ponto_coleta.php" class="active">Ponto de Coleta</a>
                    <a href="">Campanha</a>
                </div>
            </details>

            <details name="menu-nav">
                <summary>Coletas</summary>
                <div class="sub-menu menu-direita">
                    <a href="">Orientações de Descarte</a>
                    <a href="">Campanhas de Descarte</a>
                    <a href="visualizacao_ponto.php">Pontos de Coleta Próximos</a>
                </div>
            </details>
        </nav>
    </header>

    <main>

        <h2 class="box-titulo">Cadastrar Ponto de Coleta</h2>

        <?php if (!empty($erro)): ?>
            <p style="color: red; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form action="cadastro_ponto_coleta.php" method="post">
            <div class="form-option">
                <label for="cep">CEP:</label>
                <input type="text" name="cep" id="cep" maxlength="9" placeholder="00000-000" required>
            </div>

            <div class="form-option">
                <label for="materiais">Materiais:</label>
                <?php foreach ($materiais as $m): ?>
                    <label for="<?= htmlspecialchars($m['tipo']) ?>">
                        <input type="checkbox" id="<?= htmlspecialchars($m['tipo']) ?>" name="opcoes[<?= htmlspecialchars($m['tipo']) ?>]" value="<?= (int) $m['id'] ?>">
                        <?= htmlspecialchars(ucfirst($m['tipo'])) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="form-option">
                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="disponivel">Disponível</option>
                    <option value="indisponivel">Indisponível</option>
                </select>
            </div>

            <div class="form-btns">
                <button type="submit" class="btn-acao btn-confirmar">Confirmar</button>
                <a href="visualizacao_ponto.php" class="btn-acao btn-cancelar">Cancelar</a>
            </div>
        </form>

    </main>

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>

</body>

</html>