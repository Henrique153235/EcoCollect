<?php

declare(strict_types=1);

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
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect | Pontos de Coleta</title>
    <link rel="stylesheet" href="../css/visualizacao_ponto.css">
    <link rel="stylesheet" href="../css/header.css">
</head>

<body>

    <?php require_once __DIR__ . "/header.php" ?>

    <main>
        <section class="caixa-pontos">
            <div class="topo-pontos">
                <h2>Pontos de Coleta Cadastrados</h2>
                <a href="cadastro_ponto_coleta.php" class="btn-novo">Cadastrar Novo Ponto</a>
            </div>

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

                            <div class="card-actions">
                                <a href="edicao_ponto_coleta.php?id=<?= $p['id'] ?>" class="btn-editar">Editar</a>
                                <a href="remocao_ponto_coleta.php?id=<?= $p['id'] ?>" class="btn-remover">Remover</a>
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