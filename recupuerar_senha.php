<?php
session_start();
include_once 'config.php';

$errors = [];
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Validar se as senhas coincidem
    if ($nova_senha !== $confirmar_senha) {
        $errors[] = "As senhas não coincidem.";
    } elseif (strlen($nova_senha) < 6) {
        $errors[] = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        // Verificar se o email existe no banco
        $stmt = $strcon->prepare("SELECT id FROM clientes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Atualizar a senha e resetar tentativas
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = $strcon->prepare("UPDATE clientes SET senha = ?, tentativas_login = 0 WHERE email = ?");
            $stmt->bind_param("ss", $senha_hash, $email);
            if ($stmt->execute()) {
                $mensagem = "Senha alterada com sucesso! <a href='login.php'>Faça login</a>";
            } else {
                $errors[] = "Erro ao alterar a senha.";
            }
        } else {
            $errors[] = "Email não encontrado.";
        }
        $stmt->close();
    }
}

mysqli_close($strcon);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Recuperar Senha</h2>
        <?php if ($mensagem): ?>
            <p class="success"><?php echo $mensagem; ?></p>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="nova_senha">Nova Senha:</label>
                <input type="password" id="nova_senha" name="nova_senha" required>
            </div>
            <div class="form-group">
                <label for="confirmar_senha">Confirmar Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
            </div>
            <button type="submit">Alterar Senha</button>
        </form>
        <p><a href="login.php">Voltar ao Login</a></p>
    </div>
</body>
</html>