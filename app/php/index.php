<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$totalColetado = 0.0;
$totalUsuarios = 0;
$totalPontos = 0;
$campanhasAtivas = 0;

try {
    $pdo = database();

    $totalColetado = (float) $pdo->query("SELECT COALESCE(SUM(quantidadeColetada), 0) FROM Coleta")->fetchColumn();
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
    <title>EcoCollect | Inicio</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.css" integrity="sha512-x9WwyMYBnlXMNQ6kQ/Lyzu1NqIhLQKL5Oq6xByfXuRj7s9CskyCbLv/1IjqzJmXwFXWr0ov6jBV7Qbc0hh9nHg==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>
    <header class="header">
        <div class="logo">
            <h1>EcoCollect</h1>
            <h3>by Ambientify</h3>
        </div>

        <nav class="navbar">
            <a href="index.php" class="active">Inicio</a>
            <a href="dashboard.php">Dashboard</a>

            <details name="menu-nav">
                <summary>Cadastro</summary>
                <div class="sub-menu">
                    <a href="cadastro_usuario.php">Usuário</a>
                    <a href="cadastro_ponto_coleta.php">Ponto de Coleta</a>
                    <a href="">Campanha</a>
                </div>
            </details>

            <details name="menu-nav">
                <summary>Coletas</summary>
                <div class="sub-menu menu-direita">
                    <a href="">Orientações de Descarte</a>
                    <a href="">Campanhas de Descarte</a>
                    <a href="visualizacao_ponto">Pontos de Coleta Próximos</a>
                </div>
            </details>
        </nav>
    </header>

    <main>
        <section class="home autoHide">
            <div class="home-content">
                <h1>Bem-vindo a EcoCollect!</h1>
                <h3>Sua plataforma de coleta seletiva</h3>
                <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quam vitae voluptatibus deserunt alias
                    tenetur molestias provident tempora, dolor, quidem excepturi quos asperiores laudantium officia.
                    Culpa illo corrupti doloremque laboriosam ipsum?</p>
                <div class="btn-box">
                    <a href="#como-participar">Login</a>
                    <a href="#como-participar">Cadastrar-se</a>
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
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRySOOYJkJI1oCP4dRwqUam9GkpM6O9w7mOr6HN4tiACg&s=10"
                        alt="">
                </div>

                <div class="text">
                    <h2>Nossa Historia</h2>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. In illum debitis ipsum reprehenderit
                        eveniet laudantium impedit illo quaerat dolorem qui? Quis libero enim et modi esse ullam,
                        temporibus porro rerum saepe vitae facilis vel? Rerum quasi incidunt iste tempore sit quas,
                        minima exercitationem, sequi soluta unde, nihil consequatur est ex illum quo iure amet nulla!
                        Earum quisquam minima quos molestias, numquam vitae enim quasi voluptates, reiciendis amet quod
                        recusandae repellendus soluta dolorem voluptas, error nam modi laudantium maxime ut ipsam
                        corrupti! Minima esse asperiores explicabo quasi voluptate est ut impedit perspiciatis nesciunt
                        rerum, nihil debitis quia quidem labore saepe excepturi aliquam enim quo maxime cumque magni!
                        Molestias ipsam accusantium sint! Architecto recusandae perferendis dicta voluptate! Et
                        obcaecati ipsam non repudiandae. Cupiditate aperiam voluptatum natus animi, totam, commodi,
                        officia dolore perferendis mollitia quidem quis iste deserunt ratione minus unde delectus in
                        maiores sequi voluptate sint cumque. Placeat quo cum at cupiditate repudiandae, dolorum itaque
                        dolore provident nihil laboriosam. Eligendi sunt vero ut, deleniti aut magnam non quisquam
                        incidunt! Minus quos quia ex doloremque nobis quod provident et inventore. Dolore labore
                        incidunt doloribus nostrum aspernatur eligendi ipsam dolorem nulla, provident architecto
                        consequatur culpa repudiandae harum, sequi unde similique est deleniti? Dicta, modi.</p>
                </div>


            </div>

            <div class="wrapper-right autoShow">
                <div class="img-objective">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRySOOYJkJI1oCP4dRwqUam9GkpM6O9w7mOr6HN4tiACg&s=10"
                        alt="">
                </div>

                <div class="text-right">
                    <h2>Nossa Missão</h2>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. In illum debitis ipsum reprehenderit
                        eveniet laudantium impedit illo quaerat dolorem qui? Quis libero enim et modi esse ullam,
                        temporibus porro rerum saepe vitae facilis vel? Rerum quasi incidunt iste tempore sit quas,
                        minima exercitationem, sequi soluta unde, nihil consequatur est ex illum quo iure amet nulla!
                        Earum quisquam minima quos molestias, numquam vitae enim quasi voluptates, reiciendis amet quod
                        recusandae repellendus soluta dolorem voluptas, error nam modi laudantium maxime ut ipsam
                        corrupti! Minima esse asperiores explicabo quasi voluptate est ut impedit perspiciatis nesciunt
                        rerum, nihil debitis quia quidem labore saepe excepturi aliquam enim quo maxime cumque magni!
                        Molestias ipsam accusantium sint! Architecto recusandae perferendis dicta voluptate! Et
                        obcaecati ipsam non repudiandae. Cupiditate aperiam voluptatum natus animi, totam, commodi,
                        officia dolore perferendis mollitia quidem quis iste deserunt ratione minus unde delectus in
                        maiores sequi voluptate sint cumque. Placeat quo cum at cupiditate repudiandae, dolorum itaque
                        dolore provident nihil laboriosam. Eligendi sunt vero ut, deleniti aut magnam non quisquam
                        incidunt! Minus quos quia ex doloremque nobis quod provident et inventore. Dolore labore
                        incidunt doloribus nostrum aspernatur eligendi ipsam dolorem nulla, provident architecto
                        consequatur culpa repudiandae harum, sequi unde similique est deleniti? Dicta, modi.</p>
                </div>
            </div>
        </section>

        <section id="como-participar">
            <div class="Como-Participar-box autoHide">
                <div class="Como-partipipar-content">
                    <h2>Como Participar?</h2>
                    <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Repellendus blanditiis expedita beatae dolorem velit eius dicta omnis quasi rem voluptas quo, alias, tenetur delectus? Soluta, molestiae? Nobis optio sed, consectetur reiciendis error aperiam soluta qui eius provident culpa sit ullam. Consequatur saepe rerum nostrum est corporis, accusantium recusandae impedit eos, dolore perferendis consequuntur corrupti aliquid a temporibus. Repellendus tenetur hic, error eaque ducimus aut laudantium iusto, dolor numquam soluta, explicabo voluptates nostrum placeat aperiam pariatur voluptate mollitia? Nesciunt officia obcaecati molestias a iure suscipit necessitatibus, voluptas quaerat? Nemo natus laboriosam hic, saepe consequuntur quos eius iure maiores reiciendis accusantium harum nostrum placeat! Totam voluptates quas a, eaque autem eum possimus hic cumque corporis sapiente quia aspernatur. Nisi iure quae dicta unde sed quas dignissimos, natus rerum ipsa tempore necessitatibus qui delectus dolorum temporibus vitae eaque molestias, laudantium quasi quidem commodi sapiente cupiditate consequatur velit animi? Eaque fugit ipsum, voluptate, fugiat tempore hic veniam repellendus nostrum vero dolores animi delectus, alias sint repellat possimus. Quidem praesentium nemo, maiores veniam necessitatibus ab corrupti sed molestiae numquam non nihil asperiores blanditiis suscipit, iure ipsam error labore, incidunt temporibus dignissimos quaerat. Corrupti officiis aliquid eveniet blanditiis numquam non nostrum cumque perspiciatis explicabo, dolores inventore.</p>
                </div>

                <div class="btn-participa">
                    <a href="cadastro_usuario.php">Fazer Login</a>
                    <a href="visualizacao_ponto">Cadastrar-se</a>
                </div>
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
                <h3>Navegação</h3>
                <a href="index.php">Início</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="visualizacao_ponto">Pontos de Coleta Próximos</a>
                <a href="">Orientações de Descarte</a>
                <a href="">Campanhas de Descarte</a>
            </div>

            <div class="footer-col">
                <h3>Cadastros</h3>
                <a href="cadastro_usuario.php">Cadastrar Usuário</a>
                <a href="cadastro_ponto_coleta.php">Cadastrar Ponto de Coleta</a>
                <a href="">Cadastrar Campanha</a>
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