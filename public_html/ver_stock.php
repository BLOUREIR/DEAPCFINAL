<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$db = new SQLite3('/home/bloureiro/DEAPCFINAL/scripts/inventario.db');
$msg = '';

// Atualizar produto existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'editar') {
        $id = (int) $_POST['produto_id'];
        $nome = $_POST['nome'];
        $preco = (float) $_POST['preco'];
        $db->exec("UPDATE produtos SET nome = '$nome', preco = $preco WHERE id = $id");

        if (!empty($_FILES['imagem']['name'])) {
            $img_dest = "/home/bloureiro/public_html/images/produto_$id.jpg";
            move_uploaded_file($_FILES['imagem']['tmp_name'], $img_dest);
        }
        $msg = "✅ Produto atualizado.";
    } elseif ($_POST['acao'] === 'stock') {
        $id = (int) $_POST['produto_id'];
        $qtd = (int) $_POST['quantidade'];
        $db->exec("UPDATE produtos SET stock = stock + $qtd WHERE id = $id");
        $msg = "📦 Stock adicionado.";
    } elseif ($_POST['acao'] === 'novo') {
        $nome = $_POST['nome'];
        $cat = $_POST['categoria'];
        $preco = (float) $_POST['preco'];
        $stock = (int) $_POST['stock'];
        $stmt = $db->prepare('INSERT INTO produtos (nome, categoria, preco, stock) VALUES (?, ?, ?, ?)');
        $stmt->bindValue(1, $nome);
        $stmt->bindValue(2, $cat);
        $stmt->bindValue(3, $preco);
        $stmt->bindValue(4, $stock);
        $stmt->execute();
        $last_id = $db->lastInsertRowID();
        if (!empty($_FILES['imagem']['name'])) {
            $img_dest = "/home/bloureiro/public_html/images/produto_$last_id.jpg";
            move_uploaded_file($_FILES['imagem']['tmp_name'], $img_dest);
        }
        $msg = "✅ Novo produto adicionado.";
    }
}

$produtos = $db->query('SELECT * FROM produtos');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestão de Stock</title>
    <style>
        body { font-family: sans-serif; padding: 30px; background: #f0f0f0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card { background: white; padding: 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        img { max-width: 100%; max-height: 150px; margin-bottom: 10px; object-fit: contain; }
        .btn { background: #007acc; color: white; padding: 8px 16px; border: none; border-radius: 6px; text-decoration: none; cursor: pointer; margin-top: 10px; display: inline-block; }
        .btn:hover { background: #005f99; }
        form { display: flex; flex-direction: column; gap: 8px; }
        .novo { margin-bottom: 40px; background: white; padding: 20px; border-radius: 10px; max-width: 500px; }
    </style>
</head>
<body>
<a class="btn" href="<?php echo $_SESSION['user'] === 'admin' ? 'admin_menu.php' : 'func_menu.php'; ?>">← Voltar ao Menu</a>
<h2>Gestão de Stock</h2>
<?php if ($msg) echo "<p><strong>$msg</strong></p>"; ?>

<div class="novo">
<h3>➕ Adicionar Novo Produto</h3>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="acao" value="novo">
    Nome: <input type="text" name="nome" required>
    Categoria: <input type="text" name="categoria" required>
    Preço: <input type="number" step="0.01" name="preco" required>
    Stock Inicial: <input type="number" name="stock" required>
    Imagem: <input type="file" name="imagem">
    <input type="submit" value="Adicionar Produto" class="btn">
</form>
</div>

<div class="grid">
<?php while ($p = $produtos->fetchArray(SQLITE3_ASSOC)) {
    $img = "/~bloureiro/images/produto_{$p['id']}.jpg";
    $img_path = "/home/bloureiro/public_html/images/produto_{$p['id']}.jpg";
    if (!file_exists($img_path)) $img = "https://via.placeholder.com/150?text=Sem+Imagem";
    echo "<div class='card'>
            <form method='post' enctype='multipart/form-data'>
            <img src='$img'>
            <input type='hidden' name='produto_id' value='{$p['id']}'>
            <input type='hidden' name='acao' value='editar'>
            <input type='text' name='nome' value='{$p['nome']}' required>
            <p>Categoria: {$p['categoria']}</p>
            <input type='number' step='0.01' name='preco' value='{$p['preco']}' required>
            <p>Stock: {$p['stock']}</p>
            <input type='file' name='imagem'>
            <input type='submit' value='💾 Atualizar' class='btn'>
            </form>
            <form method='post'>
            <input type='hidden' name='produto_id' value='{$p['id']}'>
            <input type='hidden' name='acao' value='stock'>
            <input type='number' name='quantidade' placeholder='Adicionar stock' required>
            <input type='submit' value='➕ Adicionar Stock' class='btn'>
            </form>
          </div>";
} ?>
</div>
</body>
</html>
