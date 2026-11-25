<?php
require_once __DIR__ . '/init.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect | Orientações de Descarte</title>
    <link rel="stylesheet" href="../css/orientacoes.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="icon" type="image/png" href="../img/img-logo.jpg">
</head>

<body>

    <?php require_once __DIR__ . "/header.php"; ?>

    <main class="orientacoes-main">

        <section class="welcome-banner">
            <div class="welcome-text">
                <h2>Orientações de Descarte e Pontos de Coleta</h2>
                <p>Aprenda como preparar seus resíduos corretamente, utilizar os pontos disponíveis e cadastrar novos locais de arrecadação na plataforma.</p>
            </div>
            <div class="banner-actions">
                <a href="visualizacao_ponto.php" class="btn-banner">Explorar Pontos →</a>
            </div>
        </section>

        <section class="card-box">
            <h3 class="box-subtitulo">Como Usar um Ponto de Coleta</h3>

            <div class="passos-grid">
                <article class="passo-card">
                    <span class="passo-numero">1</span>
                    <h4>Separe e Higienize</h4>
                    <p>Divida os recicláveis por categoria (papel, plástico, vidro, metal e eletrônicos). Passe uma água rápida em embalagens com restos de alimentos para evitar mau cheiro e pragas.</p>
                </article>

                <article class="passo-card">
                    <span class="passo-numero">2</span>
                    <h4>Consulte os Pontos Ativos</h4>
                    <p>Acesse a aba de visualização de pontos, filtre pelo CEP ou tipo de material e verifique se o local escolhido está com o status <strong>Disponível</strong> antes de sair de casa.</p>
                </article>

                <article class="passo-card">
                    <span class="passo-numero">3</span>
                    <h4>Registre sua Entrega</h4>
                    <p>Ao entregar os materiais no local indicado, registre a coleta no sistema para contabilizar o peso reciclado e acompanhar o impacto positivo na sua comunidade.</p>
                </article>
            </div>
        </section>

        <section class="card-box">
            <h3 class="box-subtitulo">Como Criar e Gerenciar um Ponto de Coleta</h3>

            <div class="criacao-lista">
                <div class="criacao-item">
                    <h4>Escolha um Local Seguro e Acessível</h4>
                    <p>Defina um espaço coberto ou protegido da chuva, com fácil acesso para moradores e coletores (como portarias de condomínios, comércios parceiros, escolas ou associações de bairro).</p>
                </div>

                <div class="criacao-item">
                    <h4>Cadastre os Dados Completos na Plataforma</h4>
                    <p>No menu de cadastro de pontos, informe o CEP exato, endereço, dias e horários de funcionamento, além de marcar quais materiais o seu ponto tem estrutura para armazenar.</p>
                </div>

                <div class="criacao-item">
                    <h4>Mantenha o Status Sempre Atualizado</h4>
                    <p>Se o espaço atingir a capacidade máxima ou entrar em manutenção temporária, altere o status do ponto para <strong>Indisponível</strong> até que o esvaziamento seja concluído.</p>
                </div>

                <div class="criacao-item">
                    <h4>Organize Mutirões com Campanhas</h4>
                    <p>Precisa arrecadar um volume maior em datas específicas? Crie uma campanha vinculada ao seu ponto de coleta para divulgar metas e orientações extras aos participantes.</p>
                </div>
            </div>

            <div class="secao-acoes">
                <a href="cadastro_ponto_coleta.php" class="btn-primary">Cadastrar Novo Ponto</a>
                <a href="cadastro_campanha.php" class="btn-secondary">Criar Campanha de Coleta</a>
            </div>
        </section>

        <section class="card-box">
            <h3 class="box-subtitulo">Guia Rápido de Separação por Material</h3>

            <div class="materiais-grid">
                <div class="material-card">
                    <h4>Papel e Papelão</h4>
                    <p class="pode"><strong>Pode:</strong> Caixas dobradas, jornais, revistas, folhas de caderno e embalagens limpas.</p>
                    <p class="nao-pode"><strong>Evite:</strong> Papel higiênico, guardanapos engordurados, fotografias e papel carbono.</p>
                </div>

                <div class="material-card">
                    <h4>Plástico</h4>
                    <p class="pode"><strong>Pode:</strong> Garrafas PET, frascos de produtos de limpeza, potes, tampas e sacolas limpas.</p>
                    <p class="nao-pode"><strong>Evite:</strong> Embalagens metalizadas de salgadinhos, cabos de panela e esponjas de cozinha.</p>
                </div>

                <div class="material-card">
                    <h4>Vidro</h4>
                    <p class="pode"><strong>Pode:</strong> Garrafas, potes de conserva e frascos inteiros. Se estiver quebrado, embale em papelão grosso.</p>
                    <p class="nao-pode"><strong>Evite:</strong> Espelhos, lâmpadas, cristais, porcelanas e vidros temperados.</p>
                </div>

                <div class="material-card">
                    <h4>Metal e Eletrônicos</h4>
                    <p class="pode"><strong>Pode:</strong> Latinhas de alumínio, latas de aço, fios, cabos, pilhas, baterias e pequenos aparelhos.</p>
                    <p class="nao-pode"><strong>Evite:</strong> Latas de tinta com resíduos químicos, esponjas de aço enferrujadas e clipes.</p>
                </div>
            </div>
        </section>

    </main>

    <?php require_once __DIR__ . "/footer.php"; ?>

</body>

</html>