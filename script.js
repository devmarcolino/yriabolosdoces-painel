function abrirPedido() {
    document.getElementById("formPedido").style.display = "flex";
}

function fecharPedido() {
    document.getElementById("formPedido").style.display = "none";
}

function abrirInfos(idPedido, cliente, status, precoTotal) {
    // Preenche as informações no modal
    document.getElementById("pedidoId").textContent = idPedido;
    document.getElementById("pedidoCliente").textContent = cliente;
    document.getElementById("pedidoStatus").textContent = status;
    document.getElementById("pedidoPrecoTotal").textContent = "R$ " + precoTotal.replace(".", ",");  // Formata o valor para exibição

    // Exibe o modal
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