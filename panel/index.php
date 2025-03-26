<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include("../config.php");

// Se o usuário não estiver logado, redireciona para o login
if(!isset($_SESSION['valid'])){
    header("Location: ../index.php");
    exit(); // Importante para interromper a execução do script
}

$id = $_SESSION['id'];
$query = mysqli_query($conexao, "SELECT * FROM usuarios WHERE id='$id'");
$result = mysqli_fetch_assoc($query);

if ($result) {
    $res_name = $result['nome'];
    $res_user = $result['user'];
    $res_tel = $result['tel'];
} else {
    // Se não encontrar o usuário, força logout
    session_destroy();
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel | Yria Bolos e Doces</title>
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <img src="../assets/logo.png" alt="">
    </header>

    

    <div class="content">
        <div class="user">
            <p class="subtitle">Olá <?php echo "$res_name" ?>, seja bem-vinda a sua <br>comanda digital!</p>
            <button><a href="../logout.php"><img src="" alt=""><img src="../assets/log-out.svg" alt=""> Sair da conta</a></button>
        </div>

        <div class="orders">
            <?php
                date_default_timezone_set('America/Sao_Paulo');

                $dataHoje = date('Y-m-d');

                // Consulta para contar os pedidos do dia
                $query = "SELECT COUNT(*) AS totalPedidos FROM pedidos WHERE DATE(dataPedido) = '$dataHoje'";

                $result = mysqli_query($conexao, $query);
                $row = mysqli_fetch_assoc($result);

                // Verifica se há pedidos no dia e exibe o total
                $totalPedidos = $row['totalPedidos'] ?? 0;
            ?>

            <div class="info">
                <h2>Pedidos em aberto (<?php echo "$totalPedidos" ?>)</h2>
                <p><?php echo date('d/m/Y');?></p>
            </div>

            <div id="pedidoInfo" class="modal">
                <div class="modal-content">
                    <h3>Detalhes do Pedido</h3>
                    <p><strong>ID:</strong> <span id="pedidoId"><?php echo $idPedido; ?></span></p>
                    <p><strong>Cliente:</strong> <span id="pedidoCliente"><?php echo $cliente; ?></span></p>
                    <p><strong>Status:</strong> <span id="pedidoStatus"><?php echo $status; ?></span></p>
                    <p><strong>Valor:</strong> <span id="pedidoPrecoTotal"><?php echo "R$" . number_format($precoTotal, 2, ',', '.'); ?></span></p>
                    
                    <table class="tabela">
                        <tr>
                            <td class="title-tb">Produto</td>
                            <td class="title-tb">Sabor</td>
                            <td class="title-tb">Valor</td>
                        </tr>

                        <?php
                            // Pegando os itens do pedido
                            $sqlItens = "SELECT pi.*, p.nome AS nome, s.sabor 
                                        FROM pedido_itens pi
                                        JOIN produtos p ON pi.idProduto = p.idProduto
                                        JOIN sabores s ON pi.idSabor = s.idSabor
                                        WHERE pi.idPedido = '$idPedido'";
                            $resultItens = $conexao->query($sqlItens);

                            if ($resultItens->num_rows > 0) {
                                while ($row = $resultItens->fetch_assoc()) {
                                    $produtoNome = $row['nome'];
                                    $sabor = $row['sabor'];
                                    $precoItem = $row['precoTotal']; // Preço total do item

                                    // Exibindo as informações dos itens do pedido
                                    echo "<tr>
                                            <td class='prod-info'>$produtoNome</td>
                                            <td class='prod-info'>$sabor</td>
                                            <td class='prod-info'>R$" . number_format($precoItem, 2, ',', '.') . "</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>Nenhum item encontrado para este pedido.</td></tr>";
                            }
                        ?>
                    </table>
                    
                    <button onclick="fecharInfos()">Fechar</button>
                </div>
            </div>

            <table id="tabelaPd" class="tabela">
                <tr>
                    <td class="title-tb">ID</td>
                    <td class="title-tb">Cliente</td>
                    <td class="title-tb">Valor</td>
                    <td class="title-tb">Status</td>
                </tr>

            <?php 
                $sql = "SELECT * FROM pedidos WHERE DATE(dataPedido) = '$dataHoje'"; 
                $result = $conexao->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $idPedido = $row['idPedido'];
                        $cliente = $row['cliente'];
                        $status = $row['status'];
                        $precoTotal = $row['precototal'];

                        // Tratamento de status
                        switch ($status) {
                            case 2: $status = 'Cancelado'; break;
                            case 0: $status = 'Pendente'; break;
                            case 1: $status = 'Entregue'; break;
                            default: $status = 'Desconhecido'; break;
                        }

                        // Exibir os resultados na tabela
                        echo "<tr class='table-link' onClick='abrirInfos($idPedido, \"$cliente\", \"$status\", \"$precoTotal\")'>
                                <td class='prod-info'>$idPedido</td>
                                <td class='prod-info'>$cliente</td>
                                <td class='prod-info'>R$" . number_format($precoTotal, 2, ',', '.') . "</td>
                                <td class='prod-info'><p class='order-status'>$status</p></td>
                            </tr>";
                    }
                } else {
                    echo "<div class='message'>
                            <img src='../assets/x-square.svg'><p>Nenhum pedido encontrado.</p>
                        </div>
                         <script>
                            document.querySelector('#tabelaPd').style = 'display:none;';
                        </script>";   
                }
            ?>
            </table>
        </div>
        
        <div id="formPedido" class="modal">
            <?php
            $query_produtos = "SELECT idProduto, nome FROM produtos";
            $result_produtos = mysqli_query($conexao, $query_produtos);
            
            // Armazenar os resultados em um array
            $produtos_array = [];
            while ($produto = mysqli_fetch_assoc($result_produtos)) {
                $produtos_array[] = $produto;
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $cliente = mysqli_real_escape_string($conexao, $_POST['cliente']);

                // Criar o pedido na tabela 'pedidos'
                $query_pedido = "INSERT INTO pedidos (dataPedido, precototal, cliente, status) VALUES (NOW(), 0, '$cliente', 0)";
                mysqli_query($conexao, $query_pedido) or die(mysqli_error($conexao));

                // Pegando o ID do pedido recém-criado
                $idPedido = mysqli_insert_id($conexao);
                $totalPedido = 0;

                // Verifica se os produtos foram enviados corretamente
                if (!empty($_POST['produtos']) && !empty($_POST['sabores']) && !empty($_POST['quantidades'])) {
                    $produtos = $_POST['produtos'];
                    $sabores = $_POST['sabores'];
                    $quantidades = $_POST['quantidades'];

                    // Inserir os itens do pedido na tabela 'pedido_itens'
                    for ($i = 0; $i < count($produtos); $i++) {
                        $idProduto = (int)$produtos[$i];
                        $idSabor = (int)$sabores[$i];
                        $quantidade = (int)$quantidades[$i];

                        // Buscar preço do produto e do sabor
                        $query_preco = "SELECT preco FROM produtos WHERE idProduto = '$idProduto'";
                        $resultado_produto = mysqli_query($conexao, $query_preco);
                        $produto = mysqli_fetch_assoc($resultado_produto);
                        $precoProduto = $produto ? $produto['preco'] : 0;

                        $query_sabor = "SELECT addPreco FROM sabores WHERE idSabor = '$idSabor'";
                        $resultado_sabor = mysqli_query($conexao, $query_sabor);
                        $sabor = mysqli_fetch_assoc($resultado_sabor);
                        $precoSabor = $sabor ? $sabor['addPreco'] : 0;

                        // Calcular preço total do item
                        $precoFinal = ($precoProduto + $precoSabor) * $quantidade;
                        $totalPedido += $precoFinal;

                        // Inserir item no pedido_itens
                        $query_item = "INSERT INTO pedido_itens (idPedido, idProduto, idSabor, qnt, preco) 
                                    VALUES ('$idPedido', '$idProduto', '$idSabor', '$quantidade', '$precoFinal')";
                        mysqli_query($conexao, $query_item) or die(mysqli_error($conexao));
                    }
                }

                // Atualizar o total do pedido
                $query_update_total = "UPDATE pedidos SET precototal = '$totalPedido' WHERE idPedido = '$idPedido'";
                mysqli_query($conexao, $query_update_total) or die(mysqli_error($conexao));

                echo "<script>alert('Pedido adicionado com sucesso!'); window.location.href='../index.php';</script>";
            }
            ?>

        <div class="modal" id="produtos-container">
            <div class="modal-content">
                <h3>Adicionar Produto</h3>
                <div class="sp">
                    <label for="produto">Produto</label>
                    <select name="produto" id="produto" onchange="atualizarSabores()">
                        <option value="">Selecione um produto</option>
                        <?php foreach ($produtos_array as $produto): ?>
                            <option value="<?php echo "\"{$produto['idProduto']}\""; ?>"><?php echo "\"{$produto['nome']}\""; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sp">
                    <label for="sabor">Sabor</label>
                    <select name="sabor" id="sabor">
                        <option value="">Selecione um produto primeiro</option>
                    </select>
                </div>
                <div class="sp">
                    <label for="qtd">Quantidade</label>
                    <input type="number" name="qtd" id="qtd" required>
                </div>
                <div class="sp">
                    <button type="button" class="btn" onclick="adicionarProdutoLista()">Adicionar</button>
                    <button type="button" class="btn" onclick="fecharModal()">Fechar</button>
                </div>
            </div>
        </div>

                <div class="modal-content">
                    <div class="head-menu">
                        <h2>Adicionar Pedido</h2>
                        <span class="close" onclick="fecharPedido()">&times;</span>
                    </div>

                    <form id="menu" method="POST" action="">
                        <div class="products">
                            <h3>Produtos</h3>
                            <div class="campo-produtos" id="lista-produtos"></div>
                            <div class="sp">
                                <button type="button" class="btn" onclick="abrirModal()">Adicionar Produto</button>
                            </div>
                        </div>

                        <div class="sp">
                            <label for="cliente">Cliente</label>
                            <input type="text" id="cliente" name="cliente" required>
                        </div>

                        <input id="btn" type="submit" class="btn-primary" value="Finalizar Pedido">
                    </form>
                </div>
        </div>
        
        <div class="menu">
            <button class="large"><a href="../orders/"><img src="../assets/package.svg" alt="">Ver todos os pedidos</a></button>
            
            <button class="large" onclick="abrirPedido()"><img src="../assets/plus-square.svg" alt="">Adicionar Pedido</button>
        </div>

    </div>

    <script>
        var saboresPorProduto = <?php echo json_encode($sabores_por_produto); ?>;

        
        function atualizarSabores() {
            var produtoSelecionado = document.getElementById("produto").value;
            var selectSabor = document.getElementById("sabor");

            // Resetar o select de sabores
            selectSabor.innerHTML = '<option value="">Selecione um sabor</option>';

            if (produtoSelecionado && saboresPorProduto[produtoSelecionado]) {
                saboresPorProduto[produtoSelecionado].forEach(sabor => {
                    var option = document.createElement("option");
                    option.value = sabor.idSabor;
                    option.textContent = sabor.sabor;
                    selectSabor.appendChild(option);
                });
            }
        }
            
            function adicionarProdutoLista() {
                var produto = document.getElementById("produto");
                var sabor = document.getElementById("sabor");
                var quantidade = document.getElementById("qtd").value;
                if (produto.value && sabor.value && quantidade) {
                    var lista = document.getElementById("lista-produtos");
                    var novoProduto = document.createElement("div");
                    novoProduto.classList.add("prod-item");
                    novoProduto.innerHTML = `<h4>Nome Produto: ${produto.options[produto.selectedIndex].text}</h4>
                                            <p>Sabor Produto: ${sabor.options[sabor.selectedIndex].text}</p>
                                            <h4>Quantidade: ${quantidade}</h4>`;
                    lista.appendChild(novoProduto);
                    fecharModal();
                } else {
                    alert("Por favor, selecione um produto, sabor e quantidade.");
                }
    }
    </script>
    <script src="../script.js"></script>
</body>
</html>