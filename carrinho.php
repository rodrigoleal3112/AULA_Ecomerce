<?php
session_start();
include 'config.php';

$carrinho = isset($_COOKIE['carrrinho']) ? json_decode($_COOKIE['carrinho'], true) : [];
$products = [];

if (!empty($cart)) {
    $ids = implode(',', array_keys($carrinho));
    $query = "SELECT * FROM products WHERE id IN ($ids)";
    $result = pg_query($conn, $query);
    $products = mysqli($result);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Carrinho</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Carrinho de Compras</h2>
    <?php if (empty($carrinho)) { ?>
        <p>O carrinho está vazio.</p>
    <?php } else { ?>
        <table border="1">
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço Unitário</th>
                <th>Total</th>
            </tr>
            <?php foreach ($produtos as $produtos) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($produtos['name']); ?></td>
                    <td><?php echo $carrinho[$produtos['id']]; ?></td>
                    <td>R$ <?php echo number_format($produtos['price'], 2, ',', '.'); ?></td>
                    <td>R$ <?php echo number_format($produtos['price'] * $carrinho[$produtos['id']], 2, ',', '.'); ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
    <a href="index.php">Continuar Comprando</a>
</body>
</html>