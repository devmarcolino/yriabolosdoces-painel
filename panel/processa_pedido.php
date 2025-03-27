<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require '../config.php';

    // Garantir que o campo de produtos está sendo recebido corretamente
    if (empty($_POST['produtos'])) {
        die("Erro: Nenhum produto foi enviado!");
    }

    // Recuperar os produtos enviados
    $produtos = json_decode($_POST['produtos'], true);

    if (!is_array($produtos)) {
        die("Erro: Produtos inválidos!");
    }

    $cliente = mysqli_real_escape_string($conexao, $_POST['cliente']);

    // Criar o pedido na tabela 'pedidos'
    $query_pedido = "INSERT INTO pedidos (dataPedido, precototal, cliente, status) VALUES (NOW(), 0, '$cliente', 0)";
    if (!mysqli_query($conexao, $query_pedido)) {
        die("Erro ao criar pedido: " . mysqli_error($conexao));
    }

    $idPedido = mysqli_insert_id($conexao);
    $totalPedido = 0;

    // Inserir os itens do pedido na tabela 'pedido_itens'
    foreach ($produtos as $produto) {
        $idProduto = (int)$produto['idProduto'];
        $idSabor = (int)$produto['idSabor'];
        $quantidade = (int)$produto['quantidade'];

        // Buscar preço do produto
        $query_preco = "SELECT preco FROM produtos WHERE idProduto = $idProduto";
        $resultado_produto = mysqli_query($conexao, $query_preco);
        $produto_db = mysqli_fetch_assoc($resultado_produto);
        $precoProduto = $produto_db ? (float)$produto_db['preco'] : 0;

        // Buscar preço adicional do sabor
        $query_sabor = "SELECT addPreco FROM sabores WHERE idSabor = $idSabor";
        $resultado_sabor = mysqli_query($conexao, $query_sabor);
        $sabor_db = mysqli_fetch_assoc($resultado_sabor);
        $precoSabor = $sabor_db ? (float)$sabor_db['addPreco'] : 0;

        // Calcular preço total do item
        $precoFinal = ($precoProduto + $precoSabor) * $quantidade;
        $totalPedido += $precoFinal;

        // Inserir item no pedido_itens
        $query_item = "INSERT INTO pedido_itens (idPedido, idProduto, idSabor, qnt, preco) 
                    VALUES ($idPedido, $idProduto, $idSabor, $quantidade, $precoFinal)";
        if (!mysqli_query($conexao, $query_item)) {
            die("Erro ao adicionar item: " . mysqli_error($conexao));
        }
    }

    // Atualizar o total do pedido
    $query_update_total = "UPDATE pedidos SET precototal = $totalPedido WHERE idPedido = $idPedido";
    if (!mysqli_query($conexao, $query_update_total)) {
        die("Erro ao atualizar total: " . mysqli_error($conexao));
    }

    echo "<script>document.location.href='./';</script>";
}
