<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: visualizacao_ponto.php?erro=id_invalido");
    exit;
}

$pontosColeta = read($pdo, "ViewPontosDeColeta", "id = ?", [$id]);

if (!$pontosColeta) {
    header("Location: visualizacao_ponto.php?erro=nao_encontrado");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        delete($pdo, "PontosDeColeta", "id = ?", [$id]);
        header("Location: visualizacao_ponto.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Não foi possível excluir este ponto porque já existem coletas registradas nele. Caso não esteja mais ativo, altere o status para Indisponível na edição.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect | Excluir Ponto de Coleta</title>
    <link rel="stylesheet" href="../css/remocao_ponto_coleta.css">
</head>

<body>

    <header class="header">
        <div class="logo">
            <h1>EcoCollect</h1>
            <h3>by Ambientify</h3>
        </div>

        <nav class="navbar">
            <a href="index.php">Inicio</a>
            <a href="dashboard.php">Dashboard</a>

            <div class="menu-item">
                <span>Cadastro</span>
                <div class="sub-menu">
                    <a href="cadastro_usuario.php">Usuário</a>
                    <a href="cadastro_ponto_coleta.php">Ponto de Coleta</a>
                    <a href="">Campanha</a>
                </div>
            </div>

            <div class="menu-item">
                <span>Coletas</span>
                <div class="sub-menu menu-direita">
                    <a href="">Orientações de Descarte</a>
                    <a href="">Campanhas de Descarte</a>
                    <a href="visualizacao_ponto.php">Pontos de Coleta Próximos</a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="caixa-remocao">
            <h2>Excluir Ponto de Coleta</h2>

            <?php if (!empty($erro)): ?>
                <p class="msg-erro"><?= htmlspecialchars($erro) ?></p>
            <?php else: ?>
                <p class="mensagem-alerta">Tem certeza que deseja remover este ponto de coleta?</p>
            <?php endif; ?>

            <form action="remocao_ponto_coleta.php?id=<?= (int) $id ?>" method="post">
                <ul class="lista-detalhes">
                    <li><strong>ID:</strong> #<?= htmlspecialchars((string) ($pontosColeta['id'] ?? '')) ?></li>
                    <li>
                        <?php
                        $cep = $pontosColeta['cep'] ?? '';
                        $cep_formatado = (strlen($cep) == 8) ? substr($cep, 0, 5) . '-' . substr($cep, 5) : $cep;
                        ?>
                        <strong>CEP:</strong> <?= htmlspecialchars($cep_formatado) ?>
                    </li>
                    <li><strong>Status:</strong> <?= htmlspecialchars(ucfirst($pontosColeta['status'] ?? '')) ?></li>
                    <li><strong>Materiais:</strong> <?= htmlspecialchars($pontosColeta['materiais'] ?? '') ?></li>
                </ul>

                <div class="form-btns">
                    <button type="submit" class="btn-confirmar-exclusao">Confirmar Exclusão</button>
                    <a href="visualizacao_ponto.php" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
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