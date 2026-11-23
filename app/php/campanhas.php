<?php

require_once __DIR__ . '/init.php';

$idUsuarioLogado = isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;

$mensagemSucesso = $_SESSION['flash_sucesso'] ?? "";
$mensagemErro = $_SESSION['flash_erro'] ?? "";
unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['action'] ?? '') == 'excluir_campanha' && $idUsuarioLogado !== null) {
    $idCampanha = filter_input(INPUT_POST, 'idCampanha', FILTER_VALIDATE_INT);

    if ($idCampanha && $idCampanha > 0) {
        try {
            $stmtDel = $pdo->prepare("DELETE FROM Campanha WHERE id = :id AND idUsuario = :idUsuario");
            $stmtDel->execute([
                ':id'        => $idCampanha,
                ':idUsuario' => $idUsuarioLogado,
            ]);

            if ($stmtDel->rowCount() > 0) {
                $_SESSION['flash_sucesso'] = "Campanha removida com sucesso.";
            } else {
                $_SESSION['flash_erro'] = "Não foi possível remover esta campanha.";
            }
            header("Location: campanhas.php");
            exit;
        } catch (PDOException $e) {
            $mensagemErro = "Erro ao remover campanha: " . $e->getMessage();
        }
    }
}

$listaMateriais = readAll($pdo, "Material");

$statusFiltro = $_GET['status'] ?? '';
$materialFiltro = filter_input(INPUT_GET, 'idMaterial', FILTER_VALIDATE_INT) ?: '';
$hoje = date('Y-m-d');

$campanhas = [];

try {
    $sql = "
        SELECT DISTINCT
            v.id,
            v.idUsuario,
            v.organizador,
            v.idPonto,
            v.cepPonto,
            v.titulo,
            v.descricao,
            v.dataInicio,
            v.dataFim,
            v.criadoEm,
            v.materiaisAlvo
        FROM ViewCampanhas v
        LEFT JOIN CampanhaMaterial cm ON v.id = cm.idCampanha
        WHERE 1=1
    ";
    $params = [];

    if ($statusFiltro == 'ativas') {
        $sql .= " AND v.dataInicio <= :hojeInicio AND v.dataFim >= :hojeFim";
        $params[':hojeInicio'] = $hoje;
        $params[':hojeFim'] = $hoje;
    } elseif ($statusFiltro == 'futuras') {
        $sql .= " AND v.dataInicio > :hojeFutura";
        $params[':hojeFutura'] = $hoje;
    } elseif ($statusFiltro == 'encerradas') {
        $sql .= " AND v.dataFim < :hojeEncerrada";
        $params[':hojeEncerrada'] = $hoje;
    }

    if (!empty($materialFiltro)) {
        $sql .= " AND cm.idMaterial = :idMaterial";
        $params[':idMaterial'] = $materialFiltro;
    }

    $sql .= " ORDER BY (v.dataFim >= :hojeOrdem) DESC, v.dataInicio ASC, v.id DESC";
    $params[':hojeOrdem'] = $hoje;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $campanhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    try {
        $sqlFallback = "
            SELECT
                camp.id,
                camp.idUsuario,
                u.nome AS organizador,
                camp.idPonto,
                p.cep AS cepPonto,
                camp.titulo,
                camp.descricao,
                camp.dataInicio,
                camp.dataFim,
                camp.criadoEm,
                COALESCE(GROUP_CONCAT(DISTINCT m.tipo ORDER BY m.tipo SEPARATOR ', '), 'Todos os materiais') AS materiaisAlvo
            FROM Campanha camp
            INNER JOIN Usuarios u ON camp.idUsuario = u.id
            LEFT JOIN PontosDeColeta p ON camp.idPonto = p.id
            LEFT JOIN CampanhaMaterial cm ON camp.id = cm.idCampanha
            LEFT JOIN Material m ON cm.idMaterial = m.id
            WHERE 1=1
        ";
        $paramsFallback = [];

        if ($statusFiltro == 'ativas') {
            $sqlFallback .= " AND camp.dataInicio <= :hojeInicio AND camp.dataFim >= :hojeFim";
            $paramsFallback[':hojeInicio'] = $hoje;
            $paramsFallback[':hojeFim'] = $hoje;
        } elseif ($statusFiltro == 'futuras') {
            $sqlFallback .= " AND camp.dataInicio > :hojeFutura";
            $paramsFallback[':hojeFutura'] = $hoje;
        } elseif ($statusFiltro == 'encerradas') {
            $sqlFallback .= " AND camp.dataFim < :hojeEncerrada";
            $paramsFallback[':hojeEncerrada'] = $hoje;
        }

        if (!empty($materialFiltro)) {
            $sqlFallback .= " AND EXISTS (SELECT 1 FROM CampanhaMaterial cm2 WHERE cm2.idCampanha = camp.id AND cm2.idMaterial = :idMaterial)";
            $paramsFallback[':idMaterial'] = $materialFiltro;
        }

        $sqlFallback .= "
            GROUP BY camp.id, camp.idUsuario, u.nome, camp.idPonto, p.cep, camp.titulo, camp.descricao, camp.dataInicio, camp.dataFim, camp.criadoEm
            ORDER BY (camp.dataFim >= :hojeOrdem) DESC, camp.dataInicio ASC, camp.id DESC
        ";
        $paramsFallback[':hojeOrdem'] = $hoje;

        $stmtFb = $pdo->prepare($sqlFallback);
        $stmtFb->execute($paramsFallback);
        $campanhas = $stmtFb->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        $mensagemErro = "Aviso: certifique-se de que as tabelas Campanha e CampanhaMaterial foram criadas no banco de dados.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campanhas de Descarte | EcoCollect</title>
    <link rel="stylesheet" href="../css/campanhas.css">
    <link rel="stylesheet" href="../css/header.css">
</head>

<body>

    <?php require_once __DIR__ . "/header.php" ?>

    <main class="campanhas-main">

        <section class="welcome-banner">
            <div class="welcome-text">
                <h2>Campanhas de Descarte</h2>
                <p>Acompanhe mutirões e ações especiais de arrecadação de recicláveis ativos e programados na comunidade.</p>
            </div>
            <div class="banner-actions">
                <?php if ($idUsuarioLogado !== null): ?>
                    <a href="cadastro_campanha.php" class="btn-banner">+ Nova Campanha</a>
                <?php else: ?>
                    <a href="login_usuario.php" class="btn-banner">Entrar para Criar Campanha</a>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($mensagemSucesso)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
        <?php endif; ?>

        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($mensagemErro) ?></div>
        <?php endif; ?>

        <details class="filtro-box" <?= (!empty($statusFiltro) || !empty($materialFiltro)) ? 'open' : '' ?>>
            <summary>Filtrar Campanhas</summary>
            <form method="GET" action="campanhas.php" class="form-filtro">
                <div class="campo-filtro">
                    <label for="status">Período / Situação:</label>
                    <select name="status" id="status">
                        <option value="">Todas as campanhas</option>
                        <option value="ativas" <?= $statusFiltro == 'ativas' ? 'selected' : '' ?>>Em andamento</option>
                        <option value="futuras" <?= $statusFiltro == 'futuras' ? 'selected' : '' ?>>Em breve</option>
                        <option value="encerradas" <?= $statusFiltro == 'encerradas' ? 'selected' : '' ?>>Encerradas</option>
                    </select>
                </div>

                <div class="campo-filtro">
                    <label for="idMaterial">Material Alvo:</label>
                    <select name="idMaterial" id="idMaterial">
                        <option value="">Todos os materiais</option>
                        <?php foreach ($listaMateriais as $mat): ?>
                            <option value="<?= (int) $mat['id'] ?>" <?= ((int) $materialFiltro == (int) $mat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($mat['tipo'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="botoes-filtro">
                    <button type="submit" class="btn-filtrar">Filtrar</button>
                    <?php if (!empty($statusFiltro) || !empty($materialFiltro)): ?>
                        <a href="campanhas.php" class="btn-limpar">Limpar Filtros</a>
                    <?php endif; ?>
                </div>
            </form>
        </details>

        <?php if (empty($campanhas)): ?>
            <div class="card-box">
                <p class="empty-state">Nenhuma campanha encontrada para os critérios selecionados.</p>
            </div>
        <?php else: ?>
            <section class="grid-campanhas">
                <?php foreach ($campanhas as $camp): ?>
                    <?php
                        if ($camp['dataFim'] < $hoje) {
                            $statusLabel = 'Encerrada';
                            $statusClasse = 'status-encerrada';
                        } elseif ($camp['dataInicio'] > $hoje) {
                            $statusLabel = 'Em breve';
                            $statusClasse = 'status-futura';
                        } else {
                            $statusLabel = 'Em andamento';
                            $statusClasse = 'status-ativa';
                        }

                        $dataIniFmt = date('d/m/Y', strtotime($camp['dataInicio']));
                        $dataFimFmt = date('d/m/Y', strtotime($camp['dataFim']));

                        $localTexto = 'Consulte a descrição';
                        if (!empty($camp['idPonto'])) {
                            $cepLimpo = $camp['cepPonto'] ?? '';
                            $cepFmt = (strlen($cepLimpo) == 8) ? substr($cepLimpo, 0, 5) . '-' . substr($cepLimpo, 5) : $cepLimpo;
                            $localTexto = "Ponto #" . (int) $camp['idPonto'] . ($cepFmt !== '' ? " — CEP {$cepFmt}" : "");
                        }

                        $ehOrganizador = ($idUsuarioLogado !== null && (int) $camp['idUsuario'] === $idUsuarioLogado);
                    ?>
                    <article class="card-campanha <?= $statusClasse == 'status-encerrada' ? 'campanha-encerrada' : '' ?>">
                        <div class="campanha-topo">
                            <span class="badge-status <?= $statusClasse ?>"><?= $statusLabel ?></span>
                            <span class="campanha-periodo"><?= $dataIniFmt ?> até <?= $dataFimFmt ?></span>
                        </div>

                        <div class="campanha-corpo">
                            <h3><?= htmlspecialchars($camp['titulo']) ?></h3>
                            <p class="campanha-descricao"><?= nl2br(htmlspecialchars($camp['descricao'])) ?></p>

                            <div class="campanha-detalhes">
                                <p><strong>Local / Ponto:</strong> <?= htmlspecialchars($localTexto) ?></p>
                                <p><strong>Materiais:</strong> <span class="materiais-tag"><?= htmlspecialchars(ucfirst($camp['materiaisAlvo'])) ?></span></p>
                            </div>
                        </div>

                        <div class="campanha-rodape">
                            <span class="organizador-info">
                                Organizado por <strong><?= htmlspecialchars($camp['organizador']) ?></strong>
                                <?= $ehOrganizador ? ' (Você)' : '' ?>
                            </span>

                            <?php if ($ehOrganizador): ?>
                                <form method="POST" action="campanhas.php" onsubmit="return confirm('Deseja realmente excluir esta campanha?');">
                                    <input type="hidden" name="action" value="excluir_campanha">
                                    <input type="hidden" name="idCampanha" value="<?= (int) $camp['id'] ?>">
                                    <button type="submit" class="btn-excluir-campanha">Excluir</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

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
                <a href="campanhas.php">Campanhas de Descarte</a>
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