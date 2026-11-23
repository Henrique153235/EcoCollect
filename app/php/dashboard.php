<?php

require_once __DIR__ . '/init.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php");
    exit;
}

$idUsuarioLogado = (int) $_SESSION['usuario_id'];

$usuarioLogado = read($pdo, "Usuarios", "id = ?", [$idUsuarioLogado]);

if (!$usuarioLogado) {
    $usuarioLogado = ['id' => $idUsuarioLogado, 'nome' => 'Usuário EcoCollect', 'email' => 'usuario@ecocollect.com', 'saldoPontos' => 0.00];
}

$mensagemSucesso = $_SESSION['flash_sucesso'] ?? "";
$mensagemErro = $_SESSION['flash_erro'] ?? "";
unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'registrar_coleta') {
    $idPonto = filter_input(INPUT_POST, 'idPonto', FILTER_VALIDATE_INT);
    $idMaterial = filter_input(INPUT_POST, 'idMaterial', FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidadeColetada', FILTER_VALIDATE_FLOAT);

    if ($idPonto && $idMaterial && $quantidade && $quantidade > 0) {
        try {
            $vinculoValido = read($pdo, "PontoMaterial", "idPonto = ? AND idMaterial = ?", [$idPonto, $idMaterial]);
            $materialInfo = read($pdo, "Material", "id = ?", [$idMaterial]);

            if ($vinculoValido && $materialInfo) {
                $valorUnidade = (float) $materialInfo['valorUnidade'];
                $pontuacaoCalculada = $quantidade * $valorUnidade;

                $pdo->beginTransaction();

                create($pdo, "Coleta", [
                    'idPonto' => $idPonto,
                    'idMaterial' => $idMaterial,
                    'idUsuario' => $idUsuarioLogado,
                    'quantidadeColetada' => $quantidade,
                    'pontuacao' => $pontuacaoCalculada,
                ]);

                $stmtSaldo = $pdo->prepare("UPDATE Usuarios SET saldoPontos = saldoPontos + :pontos WHERE id = :id");
                $stmtSaldo->execute([
                    ':pontos' => $pontuacaoCalculada,
                    ':id'     => $idUsuarioLogado,
                ]);

                $pdo->commit();

                $_SESSION['flash_sucesso'] = "Coleta registrada com sucesso! Você acumulou " . number_format($pontuacaoCalculada, 2, ',', '.') . " pontos.";
                header("Location: dashboard.php");
                exit;
            } else {
                $mensagemErro = "O material selecionado não é aceito por este ponto de coleta.";
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensagemErro = "Erro ao registrar coleta: " . $e->getMessage();
        }
    } else {
        $mensagemErro = "Por favor, preencha todos os campos corretamente.";
    }
}

$stmtStats = $pdo->prepare("SELECT COUNT(id) AS totalColetas, COALESCE(SUM(pontuacao), 0) AS totalPontos FROM Coleta WHERE idUsuario = :id");
$stmtStats->execute([':id' => $idUsuarioLogado]);
$estatisticas = $stmtStats->fetch(PDO::FETCH_ASSOC);

try {
    $rankingCompleto = readAll($pdo, "ViewRankingUsuarios");
} catch (PDOException $e) {
    $stmtRank = $pdo->query("
        SELECT
            u.id,
            u.nome,
            COUNT(c.id) AS totalColetas,
            COALESCE(SUM(c.pontuacao), 0.00) AS totalPontos
        FROM Usuarios u
        LEFT JOIN Coleta c ON u.id = c.idUsuario
        GROUP BY u.id, u.nome
        ORDER BY totalPontos DESC, totalColetas DESC, u.nome ASC
    ");
    $rankingCompleto = $stmtRank->fetchAll(PDO::FETCH_ASSOC);
}
$rankingTop10 = array_slice($rankingCompleto, 0, 10);
$posicaoUsuario = null;

foreach ($rankingCompleto as $indice => $participante) {
    if ((int) $participante['id'] === $idUsuarioLogado) {
        $posicaoUsuario = $indice + 1;
        break;
    }
}

$stmtHistorico = $pdo->prepare("
    SELECT 
        c.id, 
        c.dataColeta, 
        c.quantidadeColetada, 
        c.pontuacao, 
        p.cep AS cepPonto, 
        m.tipo AS material
    FROM Coleta c
    INNER JOIN PontosDeColeta p ON c.idPonto = p.id
    INNER JOIN Material m ON c.idMaterial = m.id
    WHERE c.idUsuario = :idUsuario
    ORDER BY c.dataColeta DESC
");
$stmtHistorico->execute([':idUsuario' => $idUsuarioLogado]);
$historicoColetas = $stmtHistorico->fetchAll(PDO::FETCH_ASSOC);

$pontosDisponiveis = readAll($pdo, "PontosDeColeta", "status = ?", ['disponivel']);
$listaMateriais = readAll($pdo, "Material");

$materiaisPorPonto = readAll($pdo, "ViewMateriaisDoPonto");

$mapaMateriaisPonto = [];
foreach ($materiaisPorPonto as $item) {
    $mapaMateriaisPonto[(int) $item['idPonto']][] = [
        'id'           => (int) $item['idMaterial'],
        'tipo'         => ucfirst($item['material']),
        'valorUnidade' => number_format((float) $item['valorUnidade'], 2, ',', '.'),
    ];
}

$statusFiltro = $_GET['status'] ?? '';
$materialFiltro = $_GET['idMaterial'] ?? '';

$sqlPontos = "SELECT DISTINCT p.id, p.cep, p.status, p.materiais 
              FROM ViewPontosDeColeta p
              LEFT JOIN PontoMaterial pm ON p.id = pm.idPonto
              WHERE 1=1";
$paramsPontos = [];

if (!empty($statusFiltro)) {
    $sqlPontos .= " AND p.status = :status";
    $paramsPontos[':status'] = $statusFiltro;
}

if (!empty($materialFiltro)) {
    $sqlPontos .= " AND pm.idMaterial = :idMaterial";
    $paramsPontos[':idMaterial'] = $materialFiltro;
}

$sqlPontos .= " ORDER BY p.id ASC";
$stmtPontos = $pdo->prepare($sqlPontos);
$stmtPontos->execute($paramsPontos);
$pontosColetaFiltrados = $stmtPontos->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | EcoCollect</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/header.css">
</head>

<body>

    <?php require_once __DIR__ . "/header.php" ?>

    <main class="dashboard-main">

        <section class="welcome-banner">
            <div class="welcome-text">
                <h2>Bem-vindo(a), <?= htmlspecialchars($usuarioLogado['nome']) ?>!</h2>
                <p>Acompanhe seu impacto ecológico e registre suas coletas de materiais recicláveis.</p>
            </div>
            <div class="user-stats-cards">
                <div class="stat-card">
                    <span class="stat-value"><?= number_format($estatisticas['totalPontos'], 2, ',', '.') ?></span>
                    <span class="stat-label">Pontos Acumulados</span>
                </div>
                <div class="stat-card">
                    <span class="stat-value"><?= number_format((float) ($usuarioLogado['saldoPontos'] ?? 0), 2, ',', '.') ?></span>
                    <span class="stat-label">Saldo para Brindes</span>
                    <a href="loja.php" class="stat-link">Ir para Loja →</a>
                </div>
                <div class="stat-card">
                    <span class="stat-value"><?= (int)$estatisticas['totalColetas'] ?></span>
                    <span class="stat-label">Coletas Realizadas</span>
                </div>
            </div>
        </section>

        <?php if (!empty($mensagemSucesso)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
        <?php endif; ?>

        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($mensagemErro) ?></div>
        <?php endif; ?>

        <div class="dashboard-grid-2col">

            <section class="card-box">
                <h3 class="box-subtitulo">Registrar Nova Coleta</h3>
                <form method="POST" action="dashboard.php" class="form-coleta">
                    <input type="hidden" name="action" value="registrar_coleta">

                    <div class="form-group">
                        <label for="idPonto">Pontos de Coleta:</label>
                        <select name="idPonto" id="idPonto" required>
                            <option value="">Selecione o ponto:</option>
                            <?php foreach ($pontosDisponiveis as $ponto): ?>
                                <?php $cep_fmt = (strlen($ponto['cep']) == 8) ? substr($ponto['cep'], 0, 5) . '-' . substr($ponto['cep'], 5) : $ponto['cep']; ?>
                                <option value="<?= $ponto['id'] ?>">Ponto #<?= $ponto['id'] ?> — CEP <?= htmlspecialchars($cep_fmt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="idMaterial">Tipos de Material:</label>
                        <select name="idMaterial" id="idMaterial" required>
                            <option value="">Material:</option>
                            <?php foreach ($listaMateriais as $mat): ?>
                                <option value="<?= $mat['id'] ?>">
                                    <?= htmlspecialchars(ucfirst($mat['tipo'])) ?> (<?= number_format($mat['valorUnidade'], 2, ',', '.') ?> pts/kg)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantidadeColetada">Quantidade (Kg):</label>
                        <input type="number" step="0.01" min="0.1" name="quantidadeColetada" id="quantidadeColetada" placeholder="Ex: 2.5" required>
                    </div>

                    <button type="submit" class="btn-primary">Registrar Coleta</button>
                </form>
            </section>

            <section class="card-box">
                <h3 class="box-subtitulo">Histórico de Coletas</h3>
                <?php if (empty($historicoColetas)): ?>
                    <p class="empty-state">Você ainda não registrou nenhuma coleta.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="tabela-historico">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Ponto (CEP)</th>
                                    <th>Material</th>
                                    <th>Qtd.</th>
                                    <th>Pontos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historicoColetas as $h): ?>
                                    <?php $cep_hist = (strlen($h['cepPonto']) == 8) ? substr($h['cepPonto'], 0, 5) . '-' . substr($h['cepPonto'], 5) : $h['cepPonto']; ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($h['dataColeta'])) ?></td>
                                        <td><?= htmlspecialchars($cep_hist) ?></td>
                                        <td><span class="badge-material"><?= htmlspecialchars(ucfirst($h['material'])) ?></span></td>
                                        <td><?= number_format($h['quantidadeColetada'], 2, ',', '.') ?></td>
                                        <td class="col-pontos">+<?= number_format($h['pontuacao'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

        </div>

        <section class="card-box ranking-section">
            <div class="ranking-header">
                <h3 class="box-subtitulo">Ranking de Recicladores (Top 10)</h3>
                <?php if ($posicaoUsuario !== null): ?>
                    <span class="badge-posicao">Sua posição: #<?= $posicaoUsuario ?> de <?= count($rankingCompleto) ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($rankingTop10)): ?>
                <p class="empty-state">Nenhum usuário pontuou no ranking ainda.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="tabela-historico tabela-ranking">
                        <thead>
                            <tr>
                                <th class="col-pos">Posição</th>
                                <th>Reciclador(a)</th>
                                <th>Coletas</th>
                                <th>Pontuação Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rankingTop10 as $idx => $rankUser): ?>
                                <?php
                                $posicao = $idx + 1;
                                $ehUsuarioAtual = ((int) $rankUser['id'] === $idUsuarioLogado);
                                $classePodio = match ($posicao) {
                                    1 => 'podio-ouro',
                                    2 => 'podio-prata',
                                    3 => 'podio-bronze',
                                    default => '',
                                };
                                ?>
                                <tr class="<?= $ehUsuarioAtual ? 'linha-usuario-atual' : '' ?>">
                                    <td class="col-pos">
                                        <span class="rank-badge <?= $classePodio ?>"><?= $posicao ?>º</span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($rankUser['nome']) ?>
                                        <?php if ($ehUsuarioAtual): ?>
                                            <span class="tag-voce">Você</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int) $rankUser['totalColetas'] ?></td>
                                    <td class="col-pontos"><?= number_format((float) $rankUser['totalPontos'], 2, ',', '.') ?> pts</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-container" style="margin-top: 2rem;">
            <div class="dashboard-header">
                <h2 class="box-titulo">Explorar Pontos de Coleta</h2>
            </div>

            <details class="filtro-box" <?= (!empty($statusFiltro) || !empty($materialFiltro)) ? 'open' : '' ?>>
                <summary>Opções de Filtragem</summary>
                <form method="GET" action="dashboard.php" class="form-filtro">
                    <div class="campo-filtro">
                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="">Todos os status</option>
                            <option value="disponivel" <?= $statusFiltro === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                            <option value="indisponivel" <?= $statusFiltro === 'indisponivel' ? 'selected' : '' ?>>Indisponível</option>
                        </select>
                    </div>

                    <div class="campo-filtro">
                        <label for="idMaterialFiltro">Material:</label>
                        <select name="idMaterial" id="idMaterialFiltro">
                            <option value="">Todos os materiais</option>
                            <?php foreach ($listaMateriais as $mat): ?>
                                <option value="<?= $mat['id'] ?>" <?= $materialFiltro == $mat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($mat['tipo'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="botoes-filtro">
                        <button type="submit" class="btn-filtrar">Filtrar</button>
                        <?php if (!empty($statusFiltro) || !empty($materialFiltro)): ?>
                            <a href="dashboard.php" class="btn-limpar">Limpar Filtros</a>
                        <?php endif; ?>
                    </div>
                </form>
            </details>

            <div class="grid-cards">
                <?php if (empty($pontosColetaFiltrados)): ?>
                    <p class="sem-resultados">Nenhum ponto encontrado com os critérios selecionados.</p>
                <?php else: ?>
                    <?php foreach ($pontosColetaFiltrados as $p): ?>
                        <article class="card-ponto">
                            <div class="card-header">
                                <span class="card-id"><strong>Ponto:</strong> #<?= htmlspecialchars($p['id']) ?></span>
                                <span class="status-tag status-<?= htmlspecialchars($p['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($p['status'])) ?>
                                </span>
                            </div>

                            <div class="card-body">
                                <p class="card-info">
                                    <?php $cep_formatado = (strlen($p['cep']) === 8) ? substr($p['cep'], 0, 5) . '-' . substr($p['cep'], 5) : $p['cep']; ?>
                                    <strong>CEP:</strong> <?= htmlspecialchars($cep_formatado) ?>
                                </p>
                                <p class="card-info">
                                    <strong>Materiais Aceitos:</strong><br>
                                    <span class="materiais-lista"><?= htmlspecialchars($p['materiais']) ?></span>
                                </p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <footer class="box-footer">
        <div class="content-footer">
            <div class="footer-col">
                <h2>EcoCollect</h2>
                <span>by Ambientify</span>
                <p>Conectando pessoas e pontos de coleta seletiva para transformar o descarte consciente em impacto ambiental positivo.</p>
            </div>

            <div class="footer-col">
                <h3>Orientações</h3>
                <a href="visualizacao_ponto.php">Pontos de Coleta Próximos</a>
                <a href="">Orientações de Descarte</a>
                <a href="">Campanhas de Descarte</a>
            </div>

            <div class="footer-col">
                <h3>Instituições</h3>
                <a href="https://cempre.org.br/" target="_blank" rel="noopener noreferrer">CEMPRE - Compromisso Empresarial para Reciclagem</a>
                <a href="https://www.reciclasampa.com.br/" target="_blank" rel="noopener noreferrer">Recicla Sampa</a>
                <a href="https://ambipar.com/" target="_blank" rel="noopener noreferrer">Ambipar Group</a>
                <a href="https://www.eureciclo.com.br/" target="_blank" rel="noopener noreferrer">eureciclo</a>
            </div>

            <div class="footer-col">
                <h3>Contato</h3>
                <p>contato@ecocollect.com.br</p>
                <p>Suporte Ambientify</p>
                <p>Segunda a Sexta, 08h às 18h</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 EcoCollect by Ambientify. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>

</html>