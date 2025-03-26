<?php 
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("config.php");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel | Yria Bolos e Doces</title>
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <img src="assets/logo.png" alt="">
    </header>

    <div class="content">
        <?php 

            if(isset($_POST['submit'])){
                $user = mysqli_real_escape_string($conexao, $_POST['user']);
                $password = mysqli_real_escape_string($conexao, $_POST['senha']);

                $query = "SELECT * FROM usuarios WHERE user='$user'";
                $result = mysqli_query($conexao, $query) or die("Erro na consulta");
                $row = mysqli_fetch_assoc($result);

                // Verifica se encontrou o usuário
                if ($row) {
                    // Se as senhas NÃO estiverem criptografadas
                    if ($password == $row['senha']) { 
                        $_SESSION['valid'] = $row['user'];
                        $_SESSION['name'] = $row['nome'];
                        $_SESSION['tel'] = $row['tel'];
                        $_SESSION['id'] = $row['id'];
                        $_SESSION['senha'] = $row['senha'];

                        echo "<script>window.location.href = './panel/' </script>";
                    } else {
                        echo "<div class='message'>
                                <img src='./styles/assets/warn-error.svg'>
                                <p>Usuário ou senha incorretos</p>
                                <button onclick='closeWarn()'><img src='./styles/assets/x.svg'></button>
                            </div> <br>";
                    }
                } else {
                    echo "<div class='message'>
                            <img src='./styles/assets/warn-error.svg'>
                            <p>Usuário ou senha incorretos</p>
                            <button onclick='closeWarn()'><img src='./styles/assets/x.svg'></button>
                        </div> <br>";
                }
            }
        ?>

        <form id="login" action="" method="post">
            <div class="usuario">
                <label for="user">Usuário</label>
                <input type="text" name="user" id="user" required>
            </div>

            <div class="senha">
                <label for="senha">Senha</label>
                <input type="password" name="senha" id="senha" required>
            </div>
            
            <input type="submit" name="submit" value="Entrar" class="btn-primary">
        </form>
    </div>
</body>
</html>