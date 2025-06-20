<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$db = new SQLite3('/home/bloureiro/DEAPCFINAL/scripts/inventario.db');
$produtos = $db->query('SELECT id, nome FROM produtos');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = $_POST['produto_id'];
    $quantidade = (int) $_POST['quantidade'];
    $tipo = $_POST['tipo'];
    $data = date('Y-m-d');

    if ($tipo === 'venda') {
        $stmt = $db->prepare('SELECT stock FROM produtos WHERE id = ?');
        $stmt->bindValue(1, $produto_id);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

        if ($result && $result['stock'] >= $quantidade) {
            $novo_stock = $result['stock'] - $quantidade;
            $update = $db->prepare('UPDATE produtos SET stock = ? WHERE id = ?');
            $update->bindValue(1, $novo_stock);
            $update->bindValue(2, $produto_id);
            $update->execute();
            echo "<p>Venda registada. Novo stock: $novo_stock</p>";
        } else {
            echo "<p>Erro: Stock insuficiente.</p>";
        }
    } elseif ($tipo === 'encomenda' && $_SESSION['user'] === 'admin') {
        $estado = 'Pendente';
        $stmt = $db->prepare('INSERT INTO encomendas (produto_id, quantidade, data_encomenda, estado) VALUES (?, ?, ?, ?)');
        $stmt->bindValue(1, $produto_id);
        $stmt->bindValue(2, $quantidade);
        $stmt->bindValue(3, $data);
        $stmt->bindValue(4, $estado);
        $stmt->execute();
        echo "<p>Encomenda registada com sucesso!</p>";
    }

    echo '<p><a href="' . ($_SESSION['user'] === 'admin' ? 'admin_menu.php' : 'func_menu.php') . '">← Voltar ao Menu</a></p>';
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registar Movimento</title>
</head>
<body>
    <h2>Registar Movimento</h2>
    <form method="post">
        <label>Produto:</label>
        <select name="produto_id">
            <?php while ($row = $produtos->fetchArray(SQLITE3_ASSOC)) {
                echo "<option value='{$row['id']}'>{$row['nome']}</option>";
            } ?>
        </select><br><br>

        <label>Quantidade:</label>
        <input type="number" name="quantidade" min="1" required><br><br>

        <?php if ($_SESSION['user'] === 'admin') { ?>
            <label>Tipo:</label>
            <select name="tipo">
                <option value="venda">Venda</option>
                <option value="encomenda">Encomenda</option>
            </select><br><br>
        <?php } else { ?>
            <input type="hidden" name="tipo" value="venda">
        <?php } ?>

        <input type="submit" value="Registar">
    </form>
</body>
</html>
