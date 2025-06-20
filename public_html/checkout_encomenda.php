<?php
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$db = new SQLite3('/home/bloureiro/DEAPCFINAL/scripts/inventario.db');

if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    header('Location: encomenda.php');
    exit;
}

$carrinho = $_SESSION['carrinho'];
$produtos = [];
$total = 0;

foreach ($carrinho as $id => $qtd) {
    $stmt = $db->prepare('SELECT nome, preco FROM produtos WHERE id = ?');
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $res = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    if ($res) {
        $res['quantidade'] = $qtd;
        $res['subtotal'] = $res['preco'] * $qtd;
        $total += $res['subtotal'];
        $produtos[$id] = $res;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare('SELECT MAX(id_encomenda) as max FROM encomendas');
    $max = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    $next_id = $max['max'] ? $max['max'] + 1 : 1;

    $data = date('Y-m-d');
    foreach ($carrinho as $id => $qtd) {
        $stmt = $db->prepare('INSERT INTO encomendas (produto_id, quantidade, data_encomenda, estado, id_encomenda)
                              VALUES (?, ?, ?, "Pendente", ?)');
        $stmt->bindValue(1, $id);
        $stmt->bindValue(2, $qtd);
        $stmt->bindValue(3, $data);
        $stmt->bindValue(4, $next_id);
        $stmt->execute();
    }

    unset($_SESSION['carrinho']);
    header('Location: historico_encomendas.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Checkout da Encomenda</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 30px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        .btn { background: #007acc; color: white; padding: 10px 20px; text-decoration: none; border: none; border-radius: 6px; margin: 10px 5px; cursor: pointer; }
        .btn:hover { background: #005f99; }
        .total { font-size: 18px; font-weight: bold; padding-top: 20px; }
    </style>
</head>
<body>
<h2>Confirmação da Encomenda</h2>

<table>
    <tr><th>Produto</th><th>Preço</th><th>Quantidade</th><th>Subtotal</th></tr>
    <?php foreach ($produtos as $item): ?>
        <tr>
            <td><?= $item['nome'] ?></td>
            <td><?= number_format($item['preco'], 2) ?>€</td>
            <td><?= $item['quantidade'] ?></td>
            <td><?= number_format($item['subtotal'], 2) ?>€</td>
        </tr>
    <?php endforeach; ?>
</table>

<p class="total">Total da Encomenda: <?= number_format($total, 2) ?>€</p>

<form method="post">
    <button type="submit" class="btn">Submeter Encomenda</button>
    <a href="encomenda.php" class="btn">← Voltar</a>
</form>
</body>
</html>
