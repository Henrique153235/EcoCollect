<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$totalColetado = 0.0;
$totalUsuarios = 0;
$totalPontos = 0;
$campanhasAtivas = 0;
$nomeUsuarioLogado = '';

try {
    $pdo = database();

    if (isset($_SESSION['usuario_id'])) {
        $idUsuarioLogado = (int) $_SESSION['usuario_id'];
        if ($idUsuarioLogado > 0) {
            $usuarioLogado = read($pdo, 'Usuarios', 'id = ?', [$idUsuarioLogado]);
            if ($usuarioLogado && !empty($usuarioLogado['nome'])) {
                $nomeUsuarioLogado = trim((string) $usuarioLogado['nome']);
            }
        }
    }

    $totalColetado = (float)$pdo->query("SELECT COALESCE(SUM(quantidadeColetada), 0) FROM Coleta")->fetchColumn();
    $totalUsuarios = count(readAll($pdo, 'Usuarios'));
    $totalPontos = count(readAll($pdo, 'PontosDeColeta', 'status = ?', ['disponivel']));
    $campanhasAtivas = count(readAll($pdo, 'Campanha', 'CURDATE() BETWEEN dataInicio AND dataFim'));
} catch (Throwable $e) {
    // Mantém os indicadores zerados caso ocorra falha de conexão
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect | Início</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.css" integrity="sha512-x9WwyMYBnlXMNQ6kQ/Lyzu1NqIhLQKL5Oq6xByfXuRj7s9CskyCbLv/1IjqzJmXwFXWr0ov6jBV7Qbc0hh9nHg==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="icon" type="image/png" href="../img/img-logo.jpg">
</head>

<body>
    
<?php require_once __DIR__ . "/header.php" ?>

    <main>
        <section class="home autoHide">
            <div class="home-content">
                <h1>Bem-vindo a EcoCollect!</h1>
                <h3>Sua plataforma de coleta seletiva</h3>
                <p>Encontre pontos de descarte próximos, registre suas coletas e acumule pontos contribuindo para um meio ambiente mais limpo e sustentável.</p>
                <div class="btn-box">
                    <a href="login_usuario.php">Login</a>
                    <a href="cadastro_usuario.php">Cadastrar-se</a>
                </div>
            </div>

            <div class="box-estatisticas">
                <div class="box-grid">
                    <div class="box">
                        <i class="fa-solid fa-recycle"></i>
                        <span class="numero-estatistica"><?= number_format($totalColetado, 1, ',', '.') ?> kg</span>
                        <h3>Total Coletado</h3>
                    </div>

                    <div class="box">
                        <i class="fa-solid fa-users"></i>
                        <span class="numero-estatistica"><?= number_format($totalUsuarios, 0, ',', '.') ?></span>
                        <h3>Usuários Cadastrados</h3>
                    </div>

                    <div class="box">
                        <i class="fa-solid fa-location-dot"></i>
                        <span class="numero-estatistica"><?= number_format($totalPontos, 0, ',', '.') ?></span>
                        <h3>Pontos de Coleta</h3>
                    </div>

                    <div class="box">
                        <i class="fa-solid fa-bullhorn"></i>
                        <span class="numero-estatistica"><?= number_format($campanhasAtivas, 0, ',', '.') ?></span>
                        <h3>Campanhas Ativas</h3>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="wrapper autoShow">
                <div class="img-history">
                    <img src="../img/img-logo.jpg"
                        alt="Nossa História">
                </div>

                <div class="text">
                    <h2>Nossa História</h2>
                    <p>O EcoCollect nasceu dentro da Ambientify a partir de uma dúvida simples: por que descartar resíduos recicláveis corretamente ainda dá tanto trabalho no dia a dia? Muitas vezes, o problema não é a falta de vontade das pessoas, mas a dificuldade de encontrar informações claras e atualizadas sobre onde levar cada tipo de material. Vendo essa barreira de perto, decidimos criar uma plataforma colaborativa em que qualquer usuário ou responsável pode cadastrar, organizar e configurar pontos de coleta, definindo exatamente quais resíduos são aceitos em cada endereço e mantendo o status de disponibilidade sempre visível. Tudo isso foi pensado para funcionar de maneira rápida, intuitiva e acessível, aproximando quem quer descartar dos pontos que realmente recebem aquele material e fortalecendo a reciclagem na comunidade.</p>
                </div>
            </div>

            <div class="wrapper-right autoShow">
                <div class="img-objective">
                    <img src="../img/img-objetivo.jpg"
                        alt="Nosso Objetivo">
                </div>

                <div class="text-right">
                    <h2>Nosso Objetivo</h2>
                    <p>Nosso objetivo é tornar a coleta seletiva rápida, fácil e eficiente na rotina de todas as pessoas, transformando o descarte consciente em um hábito simples e acessível. Queremos acabar de vez com a dúvida na hora de separar o lixo, oferecendo pontos de coleta personalizáveis, informações transparentes sobre os materiais aceitos e orientações práticas para cada tipo de resíduo. Além disso, buscamos incentivar a participação ativa por meio do acompanhamento de resultados e campanhas de descarte, conectando moradores, responsáveis e iniciativas ambientais em uma mesma rede. Com tecnologia simples e colaboração direta, queremos aumentar o volume de materiais reciclados e contribuir para cidades mais limpas, organizadas e sustentáveis.</p>
                </div>
            </div>
        </section>

        <section id="como-participar">
            <div class="wrapper autoShow">
                <div class="img-participate">
                    <img src="../img/img-participar.jpg" alt="Como Participar">
                </div>

                <div class="text">
                    <h2>Como Participar?</h2>
                    <p>Participar do EcoCollect é simples, gratuito e funciona de forma totalmente colaborativa. A plataforma utiliza pontos de coleta sugeridos pelos próprios usuários para ampliar o alcance da reciclagem na cidade e facilitar o acesso aos locais de descarte. Sempre que um novo ponto é cadastrado no sistema, nossa equipe de responsáveis analisa as informações enviadas e avalia a viabilidade do endereço e dos materiais indicados; após a aprovação, o ponto é ativado e passa a aparecer no mapa e nas listagens para toda a comunidade. Além disso, para incentivar o descarte consciente no dia a dia e reconhecer quem faz a sua parte, criamos um sistema de pontuação que calcula seus pontos automaticamente com base no tipo de material e na quantidade de itens descartados. Assim, você acompanha sua evolução, participa de campanhas ativas e ajuda a manter a cidade mais limpa. É simples: quanto mais você recicla, mais pontos acumula!</p>
                </div>
            </div>
        </section>
    </main>

    <?php require_once __DIR__ . "/footer.php" ?>

</body>

</html>