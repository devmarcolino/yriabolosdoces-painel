function abrirPedido() {
    document.getElementById("formPedido").style.display = "flex";
}

function fecharPedido() {
    document.getElementById("formPedido").style.display = "none";
}

function abrirInfos(idPedido, cliente, status, precoTotal) {

    document.getElementById("pedidoId").textContent = idPedido;
    document.getElementById("pedidoCliente").textContent = cliente;
    document.getElementById("pedidoStatus").textContent = status;
    document.getElementById("pedidoPrecoTotal").textContent = "R$" + precoTotal.replace(".", ",");  // Formata o valor para exibição

    fetch("buscar_itens.php?idPedido=" + idPedido)
        .then(response => response.text())
        .then(data => {
            document.getElementById("itensPedido").innerHTML = data;
        });

    document.getElementById("pedidoInfo").style.display = "flex";
}

function fecharInfos() {
    document.getElementById("pedidoInfo").style.display = "none";
}

function abrirModal() {
    document.getElementById("produtos-container").style.display = "flex";
}

function fecharModal() {
    document.getElementById("produtos-container").style.display = "none";
}