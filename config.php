<?php

        $dbHost = 'localhost';
        $dbUsername = 'root'; // Colocar entre aspas Nome do Local do banco de dados (Normalmente o padrão é root)
        $dbPassword = ''; // Colocar entre aspas a senha que você configurou para guardar o banco de dados (caso não colocou seixar vazio)
        $dbName = 'db-yria';

    $conexao = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    mysqli_set_charset($conexao, 'utf8');


    function apagarCookies($cookiesParaApagar = []) {
        foreach ($cookiesParaApagar as $nome) {
            if (isset($_COOKIE[$nome])) {
                setcookie($nome, '', time() - 3600, '/');
            }
        }
    }
