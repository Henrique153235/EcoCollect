<?php

require_once __DIR__ . '/init.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php");
    exit;
}

$idUsuarioLogado = (int) $_SESSION['usuario_id'];

$usuarioLogado = read($pdo, "Usuarios", "id = ?", [$idUsuarioLogado]);

if (!$usuarioLogado) {
    header("Location: login_usuario.php");
    exit;
}

$mensagemSucesso = $_SESSION['flash_sucesso'] ?? "";
$mensagemErro = $_SESSION['flash_erro'] ?? "";
unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['action'] ?? '') == 'resgatar_brinde') {
    $idBrinde = filter_input(INPUT_POST, 'idBrinde', FILTER_VALIDATE_INT);

    if ($idBrinde && $idBrinde > 0) {
        try {
            $pdo->beginTransaction();

            $stmtBrinde = $pdo->prepare("SELECT * FROM Brinde WHERE id = :id AND ativo = 1 FOR UPDATE");
            $stmtBrinde->execute([':id' => $idBrinde]);
            $brinde = $stmtBrinde->fetch(PDO::FETCH_ASSOC);

            $stmtUser = $pdo->prepare("SELECT saldoPontos FROM Usuarios WHERE id = :id FOR UPDATE");
            $stmtUser->execute([':id' => $idUsuarioLogado]);
            $userAtual = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if (!$brinde) {
                $pdo->rollBack();
                $mensagemErro = "Brinde não encontrado ou indisponível no momento.";
            } elseif ((int) $brinde['estoque'] <= 0) {
                $pdo->rollBack();
                $mensagemErro = "Este brinde está esgotado no momento.";
            } elseif (!$userAtual || (float) $userAtual['saldoPontos'] < (float) $brinde['custoPontos']) {
                $pdo->rollBack();
                $mensagemErro = "Você não possui saldo de pontos suficiente para resgatar este brinde.";
            } else {
                $custo = (float) $brinde['custoPontos'];
                $codigoResgate = 'ECO-' . strtoupper(bin2hex(random_bytes(4)));

                $stmtDesconto = $pdo->prepare("UPDATE Usuarios SET saldoPontos = saldoPontos - :custo WHERE id = :id");
                $stmtDesconto->execute([
                    ':custo' => $custo,
                    ':id'    => $idUsuarioLogado,
                ]);

                $stmtEstoque = $pdo->prepare("UPDATE Brinde SET estoque = estoque - 1 WHERE id = :id");
                $stmtEstoque->execute([':id' => $idBrinde]);

                create($pdo, "ResgateBrinde", [
                    'idUsuario'     => $idUsuarioLogado,
                    'idBrinde'      => $idBrinde,
                    'pontosGastos'  => $custo,
                    'codigoResgate' => $codigoResgate,
                    'status'        => 'pendente',
                ]);

                $pdo->commit();

                $_SESSION['flash_sucesso'] = "Resgate realizado com sucesso! Apresente o código {$codigoResgate} para retirar seu brinde.";
                header("Location: loja.php");
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensagemErro = "Erro ao processar resgate: " . $e->getMessage();
        }
    } else {
        $mensagemErro = "Selecione um brinde válido para resgatar.";
    }
}

$saldoAtual = (float) ($usuarioLogado['saldoPontos'] ?? 0);
$brindesDisponiveis = [];
$historicoResgates = [];

try {
    $brindesDisponiveis = readAll($pdo, "Brinde", "ativo = ? ORDER BY custoPontos ASC", [1]);

    $stmtResgates = $pdo->prepare("
        SELECT
            r.id,
            r.pontosGastos,
            r.codigoResgate,
            r.status,
            r.dataResgate,
            b.nome AS nomeBrinde
        FROM ResgateBrinde r
        INNER JOIN Brinde b ON r.idBrinde = b.id
        WHERE r.idUsuario = :idUsuario
        ORDER BY r.dataResgate DESC
    ");
    $stmtResgates->execute([':idUsuario' => $idUsuarioLogado]);
    $historicoResgates = $stmtResgates->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagemErro = "Aviso: certifique-se de que as tabelas Brinde e ResgateBrinde foram criadas no banco de dados.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect | Loja de Brindes</title>
    <link rel="stylesheet" href="../css/loja.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="icon" type="image/png" href="../img/img-logo.jpg">
</head>

<body>

    <?php require_once __DIR__ . "/header.php" ?>

    <main class="loja-main">

        <section class="welcome-banner">
            <div class="welcome-text">
                <h2>Loja de Colletáveis</h2>
                <p>Troque seus pontos acumulados nas coletas por itens sustentáveis exclusivos sem perder sua posição no ranking.</p>
            </div>
            <div class="user-stats-cards">
                <div class="stat-card">
                    <span class="stat-value"><?= number_format($saldoAtual, 2, ',', '.') ?></span>
                    <span class="stat-label">Saldo Disponível (pts)</span>
                    <a href="dashboard.php" class="stat-link">+ Registrar Colleta</a>
                </div>
            </div>
        </section>

        <?php if (!empty($mensagemSucesso)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
        <?php endif; ?>

        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($mensagemErro) ?></div>
        <?php endif; ?>

        <section class="card-box como-funciona-section">
            <h3 class="box-subtitulo">Como funcionam os Colletáveis?</h3>
            <div class="grid-passos">
                <div class="passo-item">
                    <span class="passo-numero">1</span>
                    <h4>Recicle e Acumule</h4>
                    <p>Registre suas entregas de materiais recicláveis no <a href="dashboard.php">Dashboard</a> para somar pontos no seu saldo.</p>
                </div>
                <div class="passo-item">
                    <span class="passo-numero">2</span>
                    <h4>Escolha seu Colletável</h4>
                    <p>Use seu saldo disponível para resgatar os itens abaixo — sua pontuação no ranking geral não é descontada.</p>
                </div>
                <div class="passo-item">
                    <span class="passo-numero">3</span>
                    <h4>Retire com seu Código</h4>
                    <p>Cada resgate gera um código exclusivo no seu histórico para apresentar e retirar seu Colletável.</p>
                </div>
            </div>
        </section>

        <section class="catalogo-section">
            <h3 class="box-titulo">Colletáveis Disponíveis</h3>

            <?php if (empty($brindesDisponiveis)): ?>
                <div class="card-box">
                    <p class="empty-state">Nenhum brinde cadastrado no catálogo no momento.</p>
                </div>
            <?php else: ?>
                <div class="grid-brindes">
                    <?php foreach ($brindesDisponiveis as $brinde): ?>
                        <?php
                        $custo = (float) $brinde['custoPontos'];
                        $estoque = (int) $brinde['estoque'];
                        $podeResgatar = ($estoque > 0 && $saldoAtual >= $custo);
                        $faltamPontos = max(0, $custo - $saldoAtual);
                        ?>
                        <article class="card-brinde <?= $estoque <= 0 ? 'brinde-esgotado' : '' ?>">
                            <div class="brinde-topo">
                                <span class="badge-custo"><?= number_format($custo, 2, ',', '.') ?> pts</span>
                                <span class="badge-estoque <?= $estoque > 0 ? 'em-estoque' : 'sem-estoque' ?>">
                                    <?= $estoque > 0 ? "{$estoque} em estoque" : "Esgotado" ?>
                                </span>
                            </div>

                            <?php if (!empty($brinde['imagem'])): ?>
                                <div class="brinde-imagem">
                                    <img src="../img/brindes/<?= htmlspecialchars($brinde['imagem']) ?>"
                                         alt="<?= htmlspecialchars($brinde['nome']) ?>"
                                         loading="lazy">
                                </div>
                            <?php endif; ?>

                            <div class="brinde-corpo">
                                <h4><?= htmlspecialchars($brinde['nome']) ?></h4>
                                <p><?= htmlspecialchars($brinde['descricao'] ?? '') ?></p>
                            </div>

                            <div class="brinde-rodape">
                                <?php if ($estoque <= 0): ?>
                                    <button type="button" class="btn-resgatar btn-desabilitado" disabled>Indisponível</button>
                                <?php elseif (!$podeResgatar): ?>
                                    <button type="button" class="btn-resgatar btn-desabilitado" disabled>
                                        Faltam <?= number_format($faltamPontos, 2, ',', '.') ?> pts
                                    </button>
                                <?php else: ?>
                                    <form method="POST" action="loja.php">
                                        <input type="hidden" name="action" value="resgatar_brinde">
                                        <input type="hidden" name="idBrinde" value="<?= (int) $brinde['id'] ?>">
                                        <button type="submit" class="btn-resgatar">Resgatar Colletável</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="card-box historico-resgates-section">
            <h3 class="box-subtitulo">Histórico de Resgates</h3>

            <?php if (empty($historicoResgates)): ?>
                <p class="empty-state">Você ainda não resgatou nenhum brinde.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="tabela-historico">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Brinde</th>
                                <th>Pontos Utilizados</th>
                                <th>Código de Retirada</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historicoResgates as $resgate): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($resgate['dataResgate'])) ?></td>
                                    <td><strong><?= htmlspecialchars($resgate['nomeBrinde']) ?></strong></td>
                                    <td class="col-pontos">-<?= number_format((float) $resgate['pontosGastos'], 2, ',', '.') ?> pts</td>
                                    <td><code class="codigo-resgate"><?= htmlspecialchars($resgate['codigoResgate']) ?></code></td>
                                    <td>
                                        <span class="status-resgate status-<?= htmlspecialchars($resgate['status']) ?>">
                                            <?= ucfirst(htmlspecialchars($resgate['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <?php require_once __DIR__ . "/footer.php" ?>
</body>

</html>