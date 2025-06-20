<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new SQLite3('/home/bloureiro/DEAPCFINAL/scripts/inventario.db');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = $_POST['produto_id'];
    $quantidade = (int) $_POST['quantidade'];

    $prod = $db->query("SELECT stock FROM produtos WHERE id = $produto_id")->fetchArray(SQLITE3_ASSOC);
    if ($prod && $prod['stock'] >= $quantidade) {
        $novo = $prod['stock'] - $quantidade;
        $db->exec("UPDATE produtos SET stock = $novo WHERE id = $produto_id");
        $msg = "✅ Venda registada.";
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
        body { font-family: sans-serif; padding: 30px; background: #f4f4f4; }
        .produto { display: none; margin-top: 20px; border: 1px solid #ccc; background: white; padding: 10px; border-radius: 6px; }
        .produto img { max-width: 150px; display: block; }
        .btn { margin-top: 10px; background: #007acc; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; }
        .btn:hover { background: #005f99; }
        select, input[type=number] { padding: 5px; font-size: 14px; }
    </style>
    <script>
        function mostrarInfo() {
            const todos = document.querySelectorAll('.produto');
            todos.forEach(div => div.style.display = 'none');
            const id = document.getElementById('produto_id').value;
            const info = document.getElementById('info_' + id);
            if (info) info.style.display = 'block';
        }
    </script>
</head>
<body>
    <h2>Registar Venda</h2>
    <?php if ($msg) echo "<p><strong>$msg</strong></p>"; ?>
    <form method="post">
        <label>Produto:</label>
        <select name="produto_id" id="produto_id" onchange="mostrarInfo()" required>
            <option value="">-- Seleciona --</option>
            <?php
            $res2 = $db->query("SELECT id, nome FROM produtos");
            while ($p = $res2->fetchArray(SQLITE3_ASSOC)) {
                echo "<option value='{$p['id']}'>{$p['nome']}</option>";
            }
            ?>
        </select>

        <div id="produtos">
            <?php
            $res3 = $db->query("SELECT * FROM produtos");
            while ($p = $res3->fetchArray(SQLITE3_ASSOC)) {
                echo "<div id='info_{$p['id']}' class='produto'>
                        <p><strong>Categoria:</strong> {$p['categoria']}</p>
                        <p><strong>Preço:</strong> {$p['preco']} €</p>
                        <p><strong>Stock:</strong> {$p['stock']}</p>";
                if ($p['imagem']) {
                    echo "<img src='../images/{$p['imagem']}' alt='Imagem'>";
                }
                echo "</div>";
            }
            ?>
        </div>

        <label>Quantidade:</label>
        <input type="number" name="quantidade" min="1" required><br>

        <button type="submit" class="btn">Vender</button>
    </form>
    <br>
    <a class="btn" href="<?php echo $_SESSION['user'] === 'admin' ? 'admin_menu.php' : 'func_menu.php'; ?>">← Voltar ao Menu</a>
</body>
</html>
