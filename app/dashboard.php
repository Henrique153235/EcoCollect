<?php

require_once __DIR__ . '/init.php';

$idUsuarioLogado = $_SESSION['usuario_id'] ?? 1;

$stmtUser = $pdo->prepare("SELECT * FROM Usuarios WHERE id = :id");
$stmtUser->execute([':id' => $idUsuarioLogado]);
$usuarioLogado = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$usuarioLogado) {
    $usuarioLogado = ['id' => 1, 'nome' => 'Usuário EcoCollect', 'email' => 'usuario@ecocollect.com'];
}

$mensagemSucesso = "";
$mensagemErro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'registrar_coleta') {
    $idPonto = filter_input(INPUT_POST, 'idPonto', FILTER_VALIDATE_INT);
    $idMaterial = filter_input(INPUT_POST, 'idMaterial', FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidadeColetada', FILTER_VALIDATE_FLOAT);

    if ($idPonto && $idMaterial && $quantidade && $quantidade > 0) {
        try {
            $stmtMat = $pdo->prepare("SELECT valorUnidade FROM Material WHERE id = :id");
            $stmtMat->execute([':id' => $idMaterial]);
            $materialInfo = $stmtMat->fetch(PDO::FETCH_ASSOC);

            if ($materialInfo) {
                $valorUnidade = (float) $materialInfo['valorUnidade'];
                $pontuacaoCalculada = $quantidade * $valorUnidade;

                $sqlInsert = "INSERT INTO Coleta (idPonto, idMaterial, idUsuario, quantidadeColetada, pontuacao) 
                              VALUES (:idPonto, :idMaterial, :idUsuario, :quantidade, :pontuacao)";
                $stmtInsert = $pdo->prepare($sqlInsert);
                $stmtInsert->execute([
                    ':idPonto' => $idPonto,
                    ':idMaterial' => $idMaterial,
                    ':idUsuario' => $idUsuarioLogado,
                    ':quantidade' => $quantidade,
                    ':pontuacao' => $pontuacaoCalculada
                ]);

                $mensagemSucesso = "Coleta registrada com sucesso! Você acumulou " . number_format($pontuacaoCalculada, 2, ',', '.') . " pontos.";
            } else {
                $mensagemErro = "Material selecionado é inválido.";
            }
        } catch (PDOException $e) {
            $mensagemErro = "Erro ao registrar coleta: " . $e->getMessage();
        }
    } else {
        $mensagemErro = "Por favor, preencha todos os campos corretamente.";
    }
}

$stmtStats = $pdo->prepare("SELECT COUNT(id) AS totalColetas, COALESCE(SUM(pontuacao), 0) AS totalPontos FROM Coleta WHERE idUsuario = :id");
$stmtStats->execute([':id' => $idUsuarioLogado]);
$estatisticas = $stmtStats->fetch(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="visualizacao_ponto.css">
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>

    <header>
        <div class="nome-projeto">
            <h1>EcoCollect</h1>
            <h3>by Ambientify</h3>
        </div>

        <nav>
            <a href="index.php">Início</a>
            <a href="dashboard.php" class="active">Dashboard</a>

            <details name="menu-nav">
                <summary>Cadastro</summary>
                <div class="sub-menu">
                    <a href="">Usuário</a>
                    <a href="cadastro_ponto_coleta.php">Ponto de Coleta</a>
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
                                <?php $cep_fmt = substr($ponto['cep'], 0, 5) . '-' . substr($ponto['cep'], 5); ?>
                                <option value="<?= $ponto['id'] ?>">Ponto #<?= $ponto['id'] ?> - CEP <?= htmlspecialchars($cep_fmt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="idMaterial">Tipos de Material:</label>
                        <select name="idMaterial" id="idMaterial" required>
                            <option value="">Material:</option>
                            <?php foreach ($listaMateriais as $mat): ?>
                                <option value="<?= $mat['id'] ?>">
                                    <?= htmlspecialchars(ucfirst($mat['tipo'])) ?> (<?= number_format($mat['valorUnidade'], 2, ',', '.') ?> pts/unid)
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
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($h['dataColeta'])) ?></td>
                                        <td><?= htmlspecialchars(substr($h['cepPonto'], 0, 5) . '-' . substr($h['cepPonto'], 5)) ?></td>
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

        <section class="dashboard-container" style="margin-top: 2rem;">
            <div class="dashboard-header">
                <h2 class="box-titulo">Explorar Pontos de Coleta</h2>
            </div>

           <details>
                <summary>Opções de Filtragem</summary>
                <form method="GET" action="" class="form-filtro">
                    <div class="campo-filtro">
                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="">Todos os status</option>
                            <option value="disponivel" <?= $statusFiltro === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                            <option value="indisponivel" <?= $statusFiltro === 'indisponivel' ? 'selected' : '' ?>>Indisponível</option>
                        </select>
                    </div>

                    <div class="campo-filtro">
                        <label for="idMaterial">Material:</label>
                        <select name="idMaterial" id="idMaterial">
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
                            <a href="visualizacao_ponto.php" class="btn-limpar">Limpar Filtros</a>
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
                                    <?php $cep_formatado = substr($p['cep'], 0, 5) . '-' . substr($p['cep'], 5); ?>
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

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>

</body>

</html>