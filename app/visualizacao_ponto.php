<!-- <?php

require_once __DIR__ . "/banco/crud.php";
require_once __DIR__ . "/banco/database.php";

$pontoColeta = readAll(database(), "ViewPontosDeColeta");

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pontos de Coleta | EcoCollect</title>
    <link rel="stylesheet" href="visualizacao_ponto.css">
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

        <section class="dashboard-container">
            <div class="dashboard-header">
                <h2 class="box-titulo">Pontos de Coleta Cadastrados</h2>
                <a href="cadastro_ponto_coleta.php" class="btn-cadastrar">Cadastrar Novo Ponto</a>
            </div>

            <div class="grid-cards">
                <?php foreach ($pontoColeta as $p): ?>
                    <article class="card-ponto">
                        <div class="card-header">
                            <span class="card-id"><strong>Ponto:</strong> #<?= htmlspecialchars($p['id']) ?></span>
                        </div>

                        <div class="card-body">
                            <p class="card-info">
                            <?php $cep_formatado = substr($p['cep'], 0, 5) . '-' . substr($p['cep'], 5); ?>
                                <strong>CEP:</strong> <?= htmlspecialchars($cep_formatado) ?>
                            </p>
                            <p class="card-status">
                                <strong>Status:</strong> <?= htmlspecialchars($p['status']) ?>
                            </p>
                            <p class="card-info">
                                <strong>Materiais Aceitos:</strong>
                                <span class="materiais-lista"><?= htmlspecialchars($p['materiais']) ?></span>
                            </p>
                        </div>

                        <div class="card-actions">
                            <a href="edicao_ponto_coleta.php?id=<?= $p['id'] ?>" class="btn-acao btn-editar">Editar</a>
                            <a href="remocao_ponto_coleta.php?id=<?= $p['id'] ?>" class="btn-acao btn-remover">Remover</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>

</body>

</html> -->


<?php

require_once __DIR__ . '/init.php';

$listaMateriais = readAll($pdo, "Material");

$statusFiltro = $_GET['status'] ?? '';
$materialFiltro = $_GET['idMaterial'] ?? '';

$sql = "SELECT DISTINCT p.id, p.cep, p.status, p.materiais 
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
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pontos de Coleta | EcoCollect</title>
    <link rel="stylesheet" href="visualizacao_ponto.css">
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

    <main>

        <section class="dashboard-container">
            <div class="dashboard-header">
                <h2 class="box-titulo">Pontos de Coleta Cadastrados</h2>
                <a href="cadastro_ponto_coleta.php" class="btn-cadastrar">Cadastrar Novo Ponto</a>
            </div>

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

            <div class="grid-cards">
                <?php if (empty($pontoColeta)): ?>
                    <p class="sem-resultados">Nenhum ponto de coleta encontrado com os filtros selecionados.</p>
                <?php else: ?>
                    <?php foreach ($pontoColeta as $p): ?>
                        <article class="card-ponto">
                            <div class="card-header">
                                <span class="card-id"><strong>Ponto:</strong> #<?= htmlspecialchars($p['id']) ?></span>
                            </div>

                            <div class="card-body">
                                <p class="card-info">
                                    <?php $cep_formatado = substr($p['cep'], 0, 5) . '-' . substr($p['cep'], 5); ?>
                                    <strong>CEP:</strong> <?= htmlspecialchars($cep_formatado) ?>
                                </p>
                                <p class="card-status">
                                    <strong>Status:</strong> <?= htmlspecialchars($p['status']) ?>
                                </p>
                                <p class="card-info">
                                    <strong>Materiais Aceitos:</strong>
                                    <span class="materiais-lista"><?= htmlspecialchars($p['materiais']) ?></span>
                                </p>
                            </div>

                            <div class="card-actions">
                                <a href="edicao_ponto_coleta.php?id=<?= $p['id'] ?>" class="btn-acao btn-editar">Editar</a>
                                <a href="remocao_ponto_coleta.php?id=<?= $p['id'] ?>" class="btn-acao btn-remover">Remover</a>
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