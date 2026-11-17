<?php

require_once __DIR__ . '/init.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php");
    exit;
}

$idUsuarioLogado = (int) $_SESSION['usuario_id'];

$mensagemSucesso = $_SESSION['flash_sucesso'] ?? "";
$mensagemErro = $_SESSION['flash_erro'] ?? "";
unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);

$pontosDisponiveis = readAll($pdo, "PontosDeColeta", "status = ?", ['disponivel']);
$listaMateriais = readAll($pdo, "Material");

$tituloForm = '';
$descricaoForm = '';
$idPontoForm = '';
$dataInicioForm = date('Y-m-d');
$dataFimForm = '';
$materiaisSelecionados = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['action'] ?? '') == 'cadastrar_campanha') {
    $tituloForm = trim($_POST['titulo'] ?? '');
    $descricaoForm = trim($_POST['descricao'] ?? '');
    $idPontoRaw = $_POST['idPonto'] ?? '';
    $idPontoForm = ($idPontoRaw !== '') ? filter_var($idPontoRaw, FILTER_VALIDATE_INT) : null;
    $dataInicioForm = trim($_POST['dataInicio'] ?? '');
    $dataFimForm = trim($_POST['dataFim'] ?? '');
    $materiaisSelecionados = isset($_POST['materiais']) && is_array($_POST['materiais'])
        ? array_filter(array_map('intval', $_POST['materiais']), fn($id) => $id > 0)
        : [];

    $dtInicio = DateTime::createFromFormat('Y-m-d', $dataInicioForm);
    $dtFim = DateTime::createFromFormat('Y-m-d', $dataFimForm);
    $datasValidas = ($dtInicio && $dtInicio->format('Y-m-d') == $dataInicioForm)
                 && ($dtFim && $dtFim->format('Y-m-d') == $dataFimForm);

    if ($tituloForm == '' || mb_strlen($tituloForm) > 120) {
        $mensagemErro = "Informe um título válido para a campanha (máximo de 120 caracteres).";
    } elseif ($descricaoForm == '') {
        $mensagemErro = "Informe a descrição e as orientações da campanha.";
    } elseif (!$datasValidas) {
        $mensagemErro = "Informe datas de início e término válidas.";
    } elseif ($dataFimForm < $dataInicioForm) {
        $mensagemErro = "A data de término não pode ser anterior à data de início.";
    } elseif ($idPontoRaw !== '' && ($idPontoForm == false || $idPontoForm <= 0)) {
        $mensagemErro = "Selecione um ponto de coleta válido.";
    } else {
        try {
            $pdo->beginTransaction();

            create($pdo, "Campanha", [
                'idUsuario'  => $idUsuarioLogado,
                'idPonto'    => $idPontoForm ?: null,
                'titulo'     => $tituloForm,
                'descricao'  => $descricaoForm,
                'dataInicio' => $dataInicioForm,
                'dataFim'    => $dataFimForm,
            ]);

            $idCampanhaCriada = (int) $pdo->lastInsertId();

            if ($idCampanhaCriada > 0 && !empty($materiaisSelecionados)) {
                $materiaisUnicos = array_unique($materiaisSelecionados);
                foreach ($materiaisUnicos as $idMat) {
                    create($pdo, "CampanhaMaterial", [
                        'idCampanha' => $idCampanhaCriada,
                        'idMaterial' => $idMat,
                    ]);
                }
            }

            $pdo->commit();

            $_SESSION['flash_sucesso'] = "Campanha \"{$tituloForm}\" cadastrada com sucesso!";
            header("Location: cadastro_campanha.php");
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensagemErro = "Erro ao cadastrar campanha: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Campanha | EcoCollect</title>
    <link rel="stylesheet" href="../css/cadastro_campanha.css">
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
            <a href="loja.php">Loja</a>

            <details name="menu-nav">
                <summary>Cadastro</summary>
                <div class="sub-menu">
                    <a href="cadastro_usuario.php">Usuário</a>
                    <a href="cadastro_ponto_coleta.php">Ponto de Coleta</a>
                    <a class="pagina-atual active" href="cadastro_campanha.php">Campanha</a>
                </div>
            </details>

            <details name="menu-nav">
                <summary>Coletas</summary>
                <div class="sub-menu menu-direita">
                    <a href="">Orientações de Descarte</a>
                    <a href="campanhas.php">Campanhas de Descarte</a>
                    <a href="visualizacao_ponto.php">Pontos de Coleta Próximos</a>
                </div>
            </details>

            <details name="menu-nav">
                <summary>Perfil</summary>
                <div class="sub-menu menu-direita">
                    <a href="editar_usuario.php">Editar Perfil</a>
                    <a href="logout.php">Sair</a>
                </div>
            </details>
        </nav>
    </header>

    <main class="campanha-main">

        <section class="welcome-banner">
            <div class="welcome-text">
                <h2>Nova Campanha de Coleta</h2>
                <p>Organize mutirões e ações especiais de arrecadação de recicláveis na sua comunidade.</p>
            </div>
            <div class="banner-actions">
                <a href="campanhas.php" class="btn-banner">Ver Campanhas Ativas →</a>
            </div>
        </section>

        <?php if (!empty($mensagemSucesso)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($mensagemSucesso) ?>
                <a href="campanhas.php" class="alert-link">Visualizar na lista de campanhas →</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($mensagemErro) ?></div>
        <?php endif; ?>

        <section class="card-box">
            <h3 class="box-subtitulo">Dados da Campanha</h3>

            <form method="POST" action="cadastro_campanha.php" class="form-campanha">
                <input type="hidden" name="action" value="cadastrar_campanha">

                <div class="form-group">
                    <label for="titulo">Título da Campanha: *</label>
                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        maxlength="120"
                        placeholder="Ex: Mutirão de Reciclagem de Eletrônicos do Bairro"
                        value="<?= htmlspecialchars($tituloForm) ?>"
                        required
                    >
                </div>

                <div class="form-row-2col">
                    <div class="form-group">
                        <label for="dataInicio">Data de Início: *</label>
                        <input
                            type="date"
                            id="dataInicio"
                            name="dataInicio"
                            value="<?= htmlspecialchars($dataInicioForm) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="dataFim">Data de Término: *</label>
                        <input
                            type="date"
                            id="dataFim"
                            name="dataFim"
                            value="<?= htmlspecialchars($dataFimForm) ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="idPonto">Ponto de Coleta Vinculado (Opcional):</label>
                    <select name="idPonto" id="idPonto">
                        <option value="">Sem ponto fixo / A definir na descrição</option>
                        <?php foreach ($pontosDisponiveis as $ponto): ?>
                            <?php $cepFmt = (strlen($ponto['cep']) === 8) ? substr($ponto['cep'], 0, 5) . '-' . substr($ponto['cep'], 5) : $ponto['cep']; ?>
                            <option value="<?= (int) $ponto['id'] ?>" <?= ((string) $idPontoForm === (string) $ponto['id']) ? 'selected' : '' ?>>
                                Ponto #<?= (int) $ponto['id'] ?> — CEP <?= htmlspecialchars($cepFmt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Materiais Alvo da Campanha (Opcional):</label>
                    <p class="field-hint">Marque os materiais específicos desta campanha. Se nenhum for selecionado, ela aceitará todos os tipos de materiais.</p>
                    <div class="checkbox-grid">
                        <?php foreach ($listaMateriais as $mat): ?>
                            <?php $checked = in_array((int) $mat['id'], $materiaisSelecionados, true); ?>
                            <label class="checkbox-card">
                                <input
                                    type="checkbox"
                                    name="materiais[]"
                                    value="<?= (int) $mat['id'] ?>"
                                    <?= $checked ? 'checked' : '' ?>
                                >
                                <span><?= htmlspecialchars(ucfirst($mat['tipo'])) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição, Local e Orientações: *</label>
                    <textarea
                        id="descricao"
                        name="descricao"
                        rows="5"
                        placeholder="Descreva os horários de atendimento, ponto de encontro, metas da campanha e como preparar os materiais para entrega..."
                        required
                    ><?= htmlspecialchars($descricaoForm) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Publicar Campanha</button>
                    <a href="campanhas.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>

    </main>

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>

</body>

</html>