<?php
session_start();
include_once 'config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Listar produtos
$query = "SELECT * FROM produtos";
$result = mysqli_query($strcon, $query);

// Adicionar ao carrinho
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_carrinho'])) {
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];

    // Recuperar ou criar o carrinho nos cookies
    $carrinho = isset($_COOKIE['carrinho']) ? json_decode($_COOKIE['carrinho'], true) : [];
    $carrinho[$produto_id] = isset($carrinho[$produto_id]) ? $carrinho[$produto_id] + $quantidade : $quantidade;

    // Salvar no cookie (expira em 7 dias)
    setcookie('carrinho', json_encode($carrinho), time() + (7 * 24 * 60 * 60), "/");
    header("Location: carrinho.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Produtos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>!</h2>
        <p><a href="logout.php">Sair</a></p>
        <h3>Produtos Disponíveis</h3>
        <table>
            <tr>
                <th>Nome</th>
                <th>Preço</th>
                <th>Quantidade</th>
                <th>Ação</th>
            </tr>
            <?php while ($produto = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                    <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                            <input type="number" name="quantidade" min="1" value="1" required>
                            <button type="submit" name="add_to_carrinho">Adicionar ao Carrinho</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <p><a href="carrinho.php">Ver Carrinho</a></p>
    </div>
</body>
</html>
<?php mysqli_close($strcon); ?>