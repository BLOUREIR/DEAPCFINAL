<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$db = new SQLite3('/home/bloureiro/DEAPCFINAL/scripts/inventario.db');
$msg = '';

// Vender produto individual
if (isset($_GET['vender_produto'])) {
    $id = (int) $_GET['vender_produto'];
    $linha = $db->querySingle("SELECT produto_id, quantidade FROM encomenda_produtos WHERE id = $id AND vendido = 0", true);
    if ($linha) {
        $stock = $db->querySingle("SELECT stock FROM produtos WHERE id = {$linha['produto_id']}", true);
        if ($stock && $stock['stock'] >= $linha['quantidade']) {
            $novo = $stock['stock'] - $linha['quantidade'];
            $db->exec("UPDATE produtos SET stock = $novo WHERE id = {$linha['produto_id']}");
            $db->exec("UPDATE encomenda_produtos SET vendido = 1 WHERE id = $id");
            $db->exec("UPDATE encomendas SET estado = 'Concluída' WHERE produto_id = {$linha['produto_id']} AND id_encomenda = (SELECT id_encomenda FROM encomenda_produtos WHERE id = $id)");
            $msg = "✅ Produto vendido.";
        } else $msg = "❌ Stock insuficiente.";
    }
}

// Vender todos de uma encomenda
if (isset($_GET['vender_tudo'])) {
    $id = (int) $_GET['vender_tudo'];
    $res = $db->query("SELECT id, produto_id, quantidade FROM encomenda_produtos WHERE id_encomenda = $id AND vendido = 0");
    $vendidos = 0;
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $stock = $db->querySingle("SELECT stock FROM produtos WHERE id = {$row['produto_id']}", true);
        if ($stock && $stock['stock'] >= $row['quantidade']) {
            $novo = $stock['stock'] - $row['quantidade'];
            $db->exec("UPDATE produtos SET stock = $novo WHERE id = {$row['produto_id']}");
            $db->exec("UPDATE encomenda_produtos SET vendido = 1 WHERE id = {$row['id']}");
            $vendidos++;
        }
    }
    if ($vendidos > 0) {
        $db->exec("UPDATE encomendas SET estado = 'Concluída' WHERE id_encomenda = $id");
        $msg = "✅ Encomenda $id vendida.";
    } else $msg = "❌ Stock insuficiente.";
}

// Cancelar encomenda
if (isset($_GET['cancelar'])) {
    $id = (int) $_GET['cancelar'];
    $db->exec("DELETE FROM encomenda_produtos WHERE id_encomenda = $id");
    $db->exec("DELETE FROM encomendas WHERE id_encomenda = $id");
    $msg = "❌ Encomenda $id cancelada.";
}

// Apagar concluída
if (isset($_GET['apagar'])) {
    $id = (int) $_GET['apagar'];
    $db->exec("DELETE FROM encomenda_produtos WHERE id_encomenda = $id");
    $db->exec("DELETE FROM encomendas WHERE id_encomenda = $id");
    $msg = "🗑️ Encomenda $id apagada.";
}

$res = $db->query("SELECT ep.*, p.nome, p.categoria, p.imagem, p.preco, pr.stock
                   FROM encomenda_produtos ep
                   JOIN produtos p ON ep.produto_id = p.id
                   JOIN produtos pr ON ep.produto_id = pr.id
                   ORDER BY ep.id_encomenda DESC, ep.id");
$encomendas = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $encomendas[$row['id_encomenda']][] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Histórico de Encomendas</title>
    <style>
        body { font-family: sans-serif; background: #f0f0f0; padding: 30px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        img { width: 60px; height: 60px; object-fit: cover; }
        .btn {
            padding: 6px 12px; margin: 4px; border: none;
            background: #007acc; color: white; border-radius: 4px;
            cursor: pointer; text-decoration: none;
        }
        .btn:hover { background: #005f99; }
    </style>
</head>
<body>
    <h2>Histórico de Encomendas</h2>
    <?php if ($msg) echo "<p><strong>$msg</strong></p>"; ?>
    <a class="btn" href="<?php echo $_SESSION['user'] === 'admin' ? 'admin_menu.php' : 'func_menu.php'; ?>">← Voltar ao Menu</a>

    <?php foreach ($encomendas as $id => $linhas): ?>
        <h3>Encomenda #<?php echo $id; ?> — <?php echo date('Y-m-d'); ?></h3>
        <table>
            <tr><th>Produto</th><th>Categoria</th><th>Imagem</th><th>Preço</th><th>Qtd</th><th>Stock</th><th>Estado</th><th>Ação</th></tr>
            <?php foreach ($linhas as $linha): ?>
                <tr>
                    <td><?php echo $linha['nome']; ?></td>
                    <td><?php echo $linha['categoria']; ?></td>
                    <td><img src="images/<?php echo $linha['imagem']; ?>"></td>
                    <td><?php echo number_format($linha['preco'], 2); ?> €</td>
                    <td><?php echo $linha['quantidade']; ?></td>
                    <td><?php echo $linha['stock']; ?></td>
                    <td><?php echo $linha['vendido'] ? '✅ Vendido' : '🕒 Pendente'; ?></td>
                    <td>
                        <?php if (!$linha['vendido']): ?>
                            <a class="btn" href="?vender_produto=<?php echo $linha['id']; ?>">Vender</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <a class="btn" href="?vender_tudo=<?php echo $id; ?>">✔️ Vender Tudo</a>
        <a class="btn" href="?cancelar=<?php echo $id; ?>">❌ Cancelar Encomenda</a>
        <a class="btn" href="?apagar=<?php echo $id; ?>">🗑️ Apagar</a>
    <?php endforeach; ?>
</body>
</html>
