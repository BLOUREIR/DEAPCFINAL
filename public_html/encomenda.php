<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$db = new SQLite3('/home/bloureiro/DEAPCFINAL/scripts/inventario.db');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtos = $_POST['produto'];
    $quantidades = $_POST['quantidade'];
    $data = date('Y-m-d');
    
    $id_enc = $db->querySingle("SELECT MAX(id_encomenda) FROM encomenda_produtos") + 1;

    foreach ($produtos as $index => $produto_id) {
        $produto_id = (int)$produto_id;
        $quantidade = (int)$quantidades[$index];
        if ($quantidade <= 0) continue;

        $stmt = $db->prepare("INSERT INTO encomenda_produtos (id_encomenda, produto_id, quantidade) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $id_enc, SQLITE3_INTEGER);
        $stmt->bindValue(2, $produto_id, SQLITE3_INTEGER);
        $stmt->bindValue(3, $quantidade, SQLITE3_INTEGER);
        $stmt->execute();

        $db->exec("INSERT INTO encomendas (produto_id, quantidade, data_encomenda, estado, id_encomenda)
                   VALUES ($produto_id, $quantidade, '$data', 'Pendente', $id_enc)");
    }

    header("Location: checkout_encomenda.php?id=$id_enc");
    exit;
}

$produtos = $db->query("SELECT * FROM produtos");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fazer Encomenda</title>
    <style>
        body { font-family: sans-serif; background: #f0f0f0; padding: 30px; }
        h2 { text-align: center; }
        form { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; }
        .item { display: flex; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        img { width: 80px; height: 80px; object-fit: cover; margin-right: 15px; border-radius: 6px; }
        .info { flex: 1; }
        .quant { width: 60px; }
        .btn { margin-top: 20px; padding: 10px 20px; font-size: 16px; background: #007acc; color: white; border: none; border-radius: 6px; cursor: pointer; }
        .btn:hover { background: #005f99; }
        .top-btn { margin-bottom: 20px; display: inline-block; }
    </style>
</head>
<body>
    <a href="<?php echo $_SESSION['user'] === 'admin' ? 'admin_menu.php' : 'func_menu.php'; ?>" class="btn top-btn">← Voltar ao Menu</a>
    <h2>Fazer Encomenda</h2>

    <form method="post">
        <?php while ($row = $produtos->fetchArray(SQLITE3_ASSOC)): ?>
            <div class="item">
                <img src="images/<?php echo htmlspecialchars($row['imagem']); ?>" alt="">
                <div class="info">
                    <strong><?php echo htmlspecialchars($row['nome']); ?></strong><br>
                    Categoria: <?php echo htmlspecialchars($row['categoria']); ?><br>
                    Preço: <?php echo number_format($row['preco'], 2); ?> €
                </div>
                <input type="hidden" name="produto[]" value="<?php echo $row['id']; ?>">
                <input type="number" class="quant" name="quantidade[]" min="0" value="0">
            </div>
        <?php endwhile; ?>

        <button class="btn" type="submit">Submeter Encomenda</button>
    </form>
</body>
</html>
