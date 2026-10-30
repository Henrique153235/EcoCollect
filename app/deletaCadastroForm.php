<?php
    require_once __DIR__ . "/banco/crud.php";
    require_once __DIR__ . "/banco/database.php";

    $Usuarios = null;

    if($_SERVER['REQUEST_METHOD'] == "GET" && isset ($_GET['id'])){
        $Usuarios = read(database(), "Usuarios" , "id = {$_GET['id']}");
    }

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        delete(database(), "Usuarios", "id = {$_GET['id']}");
        header("Location: testeCriarUsuario.php");
        exit;
    }

?>

<html>
    <head>

    </head>
    <body>
        <form action="" method="post">
            <h1>Nome: <?= $Usuarios['nome'] ?></h1><br>
            <ul>
                <li>E-mail: <?= $Usuarios['email']?></li>
                <li>Telefone: <?= $Usuarios['telefone']?></li>
                <li>Perfil: <?= $Usuarios['perfil']?></li>
            </ul>

            <button type="submit">Deletar</button>
        </form>
    </body>
</html>