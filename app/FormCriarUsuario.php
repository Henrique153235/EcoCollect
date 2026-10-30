<?php
    require_once __DIR__ . "/banco/crud.php";
    require_once __DIR__ . "/banco/database.php";

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $novoUsuario = [
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'telefone' => $_POST['telefone'],
            'perfil' => $_POST['perfil']         
            ];
        
        create(database() , "Usuarios", $novoUsuario);
        header("Location: testeCriarUsuario.php");
        exit;
    }    
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Usuarios | EcoCollect </title>
</head>
<body>
    <form action="" method="post">

            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>
            <br><br>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>
            <br><br>

            <label for="telefone">Telefone:</label>
            <input type="tel" id="telefone" name="telefone" required>
            <br><br>

            <input type="radio" id="usuario" name="perfil" value="usuario" checkbox>
            <label for="usuario">Usuário: </label>

            <input type="radio" id="responsavel" name="perfil" value="responsavel">
            <label for="responsavel">Responsável: </label>
            <br><br>

            <button type="submit">Enviar Cadastro</button>
        </form>
</body>
</html>