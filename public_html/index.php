<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $password = $_POST['password'];

    if ($tipo === 'admin' && $password === '1234') {
        $_SESSION['user'] = 'admin';
        header('Location: admin_menu.php');
        exit;
    } elseif ($tipo === 'func' && $password === '1') {
        $_SESSION['user'] = 'func';
        header('Location: func_menu.php');
        exit;
    } else {
        $erro = "Credenciais inválidas.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
    <form method="post">
        <label>Tipo:</label>
        <select name="tipo">
            <option value="admin">Administrador</option>
            <option value="func">Funcionário</option>
        </select><br><br>

        <label>Password:</label>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Entrar">
    </form>
</body>
</html>
