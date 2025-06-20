// Mostrar/ocultar info do grupo
function toggleInfo() {
    const info = document.getElementById("infoGrupo");
    info.style.display = (info.style.display === "none") ? "block" : "none";
}

// Validação simples do formulário de venda
function validarVenda() {
    const quantidade = document.querySelector('input[name="quantidade"]').value;
    if (quantidade <= 0) {
        alert("A quantidade deve ser maior que 0");
        return false;
    }
    return true;
}

// Adicionar dinamicamente uma mensagem no DOM
function adicionarMensagem(msg) {
    const div = document.createElement("div");
    div.innerText = msg;
    div.style.marginTop = "20px";
    div.style.fontWeight = "bold";
    document.body.appendChild(div);
}
