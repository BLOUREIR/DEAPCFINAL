<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$db = new SQLite3('/home/bloureiro/DEAPCFINAL/scripts/inventario.db');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['produto'];
    $qtd = $_POST['quantidade'];
    $prod = $db->query("SELECT stock FROM produtos WHERE id = $id")->fetchArray(SQLITE3_ASSOC);
    if ($prod && $prod['stock'] >= $qtd) {
        $novo = $prod['stock'] - $qtd;
        $db->exec("UPDATE produtos SET stock = $novo WHERE id = $id");
        $msg = "✅ Venda registada com sucesso.";
    } else {
        $msg = "❌ Stock insuficiente.";
    }
}
$res = $db->query("SELECT * FROM produtos");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registar Venda</title>
    <style>
        body { font-family: sans-serif; background: #f0f0f0; padding: 30px; }
        .form-box { background: white; padding: 20px; border-radius: 8px; max-width: 500px; margin: auto; }
        .form-box h2 { text-align: center; }
        label { display: block; margin-top: 10px; }
        select, input[type=number] { width: 100%; padding: 8px; margin-top: 5px; }
        .btn { margin-top: 15px; padding: 10px 20px; background: #007acc; color: white; border: none; border-radius: 6px; cursor: pointer; width: 100%; }
        .btn:hover { background: #005f99; }
        #infoProduto { margin-top: 20px; background: #eef; padding: 10px; border-radius: 6px; }
        img { max-width: 100px; max-height: 100px; display: block; margin-top: 10px; }
        .voltar { text-align: center; margin-top: 20px; }
        .voltar a { text-decoration: none; color: white; background: gray; padding: 8px 16px; border-radius: 6px; }
    </style>
</head>
<body>
<div class="form-box">
    <h2>Registar Venda</h2>
    <?php if ($msg) echo "<p><strong>$msg</strong></p>"; ?>
    <form method="post">
        <label for="produto">Selecionar Produto:</label>
        <select name="produto" id="produto" onchange="mostrarInfoProduto()" required>
            <option value="">-- Escolher --</option>
            <?php while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                echo "<option value='{$row['id']}' 
                    data-nome='{$row['nome']}' 
                    data-preco='{$row['preco']}' 
                    data-img='{$row['imagem']}' 
                    data-cat='{$row['categoria']}'>
                    {$row['nome']}</option>";
            } ?>
        </select>

        <div id="infoProduto" style="display:none;">
            <p><strong>Nome:</strong> <span id="nome"></span></p>
            <p><strong>Categoria:</strong> <span id="cat"></span></p>
            <p><strong>Preço:</strong> <span id="preco"></span> €</p>
            <img id="img" src="">
        </div>

        <label for="quantidade">Quantidade:</label>
        <input type="number" name="quantidade" min="1" required>

        <input class="btn" type="submit" value="Registar Venda">
    </form>
    <div class="voltar">
        <a href="<?php echo $_SESSION['user'] === 'admin' ? 'admin_menu.php' : 'func_menu.php'; ?>">← Voltar ao Menu</a>
    </div>
</div>

<script>
function mostrarInfoProduto() {
    var s = document.getElementById('produto');
    var opt = s.options[s.selectedIndex];
    document.getElementById('infoProduto').style.display = 'block';
    document.getElementById('nome').innerText = opt.getAttribute('data-nome');
    document.getElementById('cat').innerText = opt.getAttribute('data-cat');
    document.getElementById('preco').innerText = opt.getAttribute('data-preco');
    document.getElementById('img').src = "images/" + opt.getAttribute('data-img');
}
</script>
</body>
</html>
