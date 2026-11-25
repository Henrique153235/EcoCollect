<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$idUsuarioLogado = isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;

$mensagemSucesso = $_SESSION['flash_sucesso'] ?? "";
$mensagemErro = $_SESSION['flash_erro'] ?? "";
unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);

$listaMateriais = readAll($pdo, "Material");

$statusFiltro = $_GET['status'] ?? '';
$materialFiltro = $_GET['idMaterial'] ?? '';

$sql = "SELECT DISTINCT p.id, p.idUsuario, p.criador, p.cep, p.status, p.materiais 
        FROM ViewPontosDeColeta p
        LEFT JOIN PontoMaterial pm ON p.id = pm.idPonto
        WHERE 1=1";

$params = [];

if (!empty($statusFiltro)) {
    $sql .= " AND p.status = :status";
    $params[':status'] = $statusFiltro;
}

if (!empty($materialFiltro)) {
    $sql .= " AND pm.idMaterial = :idMaterial";
    $params[':idMaterial'] = $materialFiltro;
}

$sql .= " ORDER BY p.id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pontoColeta = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect | Pontos de Coleta</title>
    <link rel="stylesheet" href="../css/visualizacao_ponto.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="icon" type="image/png" href="../img/img-logo.jpg">
</head>

<body>

    <?php require_once __DIR__ . "/header.php" ?>

    <main>
        <section class="caixa-pontos">
            <div class="topo-pontos">
                <h2>Pontos de Coleta Cadastrados</h2>
                <?php if ($idUsuarioLogado !== null): ?>
                    <a href="cadastro_ponto_coleta.php" class="btn-novo">Cadastrar Novo Ponto</a>
                <?php else: ?>
                    <a href="login_usuario.php" class="btn-novo">Entrar para Cadastrar Ponto</a>
                <?php endif; ?>
            </div>

            <?php if (!empty($mensagemSucesso)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($mensagemErro)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($mensagemErro) ?></div>
            <?php endif; ?>

            <details class="filtro-box" <?= (!empty($statusFiltro) || !empty($materialFiltro)) ? 'open' : '' ?>>
                <summary>Opções de Filtragem</summary>
                <form method="GET" action="visualizacao_ponto.php" class="form-filtro">
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
                <?php if (empty($pontoColeta)): ?>
                    <p class="sem-resultados">Nenhum ponto de coleta encontrado com os filtros selecionados.</p>
                <?php else: ?>
                    <?php foreach ($pontoColeta as $p): ?>
                        <?php $ehCriador = ($idUsuarioLogado !== null && (int) ($p['idUsuario'] ?? 0) === $idUsuarioLogado); ?>
                        <article class="card-ponto">
                            <div class="card-header">
                                <span class="card-id"><strong>Ponto:</strong> #<?= htmlspecialchars((string) $p['id']) ?></span>
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
                                    <strong>Materiais Aceitos:</strong>
                                    <span class="materiais-lista"><?= htmlspecialchars($p['materiais']) ?></span>
                                </p>
                            </div>

                            <div class="card-rodape">
                                <span class="criador-info">
                                    Cadastrado por <strong><?= htmlspecialchars($p['criador'] ?? 'Sistema') ?></strong>
                                    <?= $ehCriador ? ' (Você)' : '' ?>
                                </span>

                                <?php if ($ehCriador): ?>
                                    <div class="card-actions">
                                        <a href="edicao_ponto_coleta.php?id=<?= (int) $p['id'] ?>" class="btn-editar">Editar</a>
                                        <a href="remocao_ponto_coleta.php?id=<?= (int) $p['id'] ?>" class="btn-remover">Remover</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php require_once __DIR__ . "/footer.php" ?>

</body>

</html>