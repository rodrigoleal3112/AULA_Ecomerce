<?php
session_start();
include_once 'config.php';

$errors = [];
$tentativas_maximas = 3;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['password']; // Ajustado para corresponder ao name="password" no formulário

    // Conexão com o banco já está em config.php como $strcon
    if ($strcon->connect_error) {
        die("Erro na conexão: " . $strcon->connect_error);
    }

    // Verificar tentativas de login
    $stmt = $strcon->prepare("SELECT id, nome, senha, tentativas_login FROM clientes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        $tentativas = $usuario['tentativas_login'];

        if ($tentativas >= $tentativas_maximas) {
            $errors[] = "Conta bloqueada. <a href='recuperar_senha.php?email=" . urlencode($email) . "'>Recuperar senha</a>";
        } elseif (password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            // Resetar tentativas
            $stmt = $strcon->prepare("UPDATE clientes SET tentativas_login = 0 WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            header("Location: index.php");
            exit();
        } else {
            // Senha incorreta
            $tentativas++;
            $stmt = $strcon->prepare("UPDATE clientes SET tentativas_login = ? WHERE email = ?");
            $stmt->bind_param("is", $tentativas, $email);
            $stmt->execute();
            $errors[] = "Senha incorreta. Tentativas restantes: " . ($tentativas_maximas - $tentativas);
        }
    } else {
        $errors[] = "Email não encontrado.";
    }
    $stmt->close();
    mysqli_close($strcon);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['mensagem'])): ?>
            <p class="success"><?php echo htmlspecialchars($_SESSION['mensagem']); unset($_SESSION['mensagem']); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
        <p><a href="cadastro.php">Não tem conta? Cadastre-se</a></p>
        <p><a href="recuperar_senha.php">Esqueceu sua senha?</a></p>
    </div>
</body>
</html>