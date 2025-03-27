<?php
include("../config.php"); // Certifique-se de incluir a conexão correta

if (isset($_GET['idPedido'])) {
    $idPedido = $_GET['idPedido'];

    $sqlItens = "SELECT pi.*, p.nome AS nome, s.sabor 
                FROM pedido_itens pi
                JOIN produtos p ON pi.idProduto = p.idProduto
                JOIN sabores s ON pi.idSabor = s.idSabor
                WHERE pi.idPedido = ?";
    
    $stmt = $conexao->prepare($sqlItens);
    $stmt->bind_param("i", $idPedido);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td class='prod-info'>{$row['nome']}</td>
                    <td class='prod-info'>{$row['qnt']}</td>
                    <td class='prod-info'>{$row['sabor']}</td>
                    <td class='prod-info'>R$" . number_format($row['preco'], 2, ',', '.') . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>Nenhum item encontrado para este pedido.</td></tr>";
    }
}
?>
