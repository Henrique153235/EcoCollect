<?php

    require_once __DIR__ . "/banco/crud.php";
    require_once __DIR__ . "/banco/database.php";

    $usuarios = readAll(database(), "Usuarios");

?>

<html>
    <head></head>
    <body>
        <a href="FormCriarUsuario.php">Criar um Usuario</a>
        <br>
        <br>

        <table border="1">
            <thead>
                <tr>
                    <td>ID</td>
                    <td>Nome</td>
                    <td>E-mail</td>
                    <td>Telefone</td>
                    <td>Perfil</td>
                </tr>
            </thead>

            <tbody>
                <?php foreach($usuarios as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= $u['nome'] ?></td>
                        <td><?= $u['email'] ?></td>
                        <td><?= $u['telefone'] ?></td>
                        <td><?= $u['perfil'] ?></td>
                        <td>
                            <a href="editaCadastroForm.php?id=<?= $u['id'] ?>">Editar</a>
                            <a href="deletaCadastroForm.php?id=<?= $u['id'] ?>">Deletar</a>
                        </td>
                    </tr>


                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
</html>