<?php
session_start();
$uploadDir = 'uploads/';
$errors = [];

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Validar foto de perfil (máximo 2MB)
    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
        $fotoExt = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (!in_array($fotoExt, ['jpg', 'jpeg', 'png'])) {
            $errors[] = "A foto deve ser JPG, JPEG ou PNG.";
        } else {
            $fotoNome = uniqid() . '.' . $fotoExt;
            $fotoPath = $uploadDir . $fotoNome;
        }
    } else {
        $errors[] = "Foto inválida ou maior que 2MB.";
    }

    // Validar documento PDF (máximo 5MB)
    if ($_FILES['documento']['error'] === UPLOAD_ERR_OK && $_FILES['documento']['size'] <= 5 * 1024 * 1024) {
        $docExt = strtolower(pathinfo($_FILES['documento']['name'], PATHINFO_EXTENSION));
        if ($docExt !== 'pdf') {
            $errors[] = "O documento deve ser um PDF.";
        } else {
            $docNome = uniqid() . '.pdf';
            $docPath = $uploadDir . $docNome;
        }
    } else {
        $errors[] = "Documento PDF inválido ou maior que 5MB.";
    }

    // Conexão com o banco
    $conn = new mysqli('localhost', 'root', '', 'sistema_clientes');
    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }

    // Verificar email duplicado
    $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors[] = "Email já cadastrado.";
    }
    $stmt->close();

    // Salvar dados e arquivos
    if (empty($errors)) {
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $fotoPath) &&
            move_uploaded_file($_FILES['documento']['tmp_name'], $docPath)) {
            $stmt = $conn->prepare("INSERT INTO clientes (nome, email, senha, foto_perfil, documento_pdf) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nome, $email, $senha, $fotoPath, $docPath);
            if ($stmt->execute()) {
                $_SESSION['mensagem'] = "Cadastro realizado com sucesso!";
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Erro ao salvar no banco.";
            }
            $stmt->close();
        } else {
            $errors[] = "Erro ao salvar arquivos.";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Cadastro de Cliente</h2>
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <div class="form-group">
                <label for="foto">Foto de Perfil (JPG/PNG):</label>
                <input type="file" id="foto" name="foto" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="documento">Documento (PDF):</label>
                <input type="file" id="documento" name="documento" accept=".pdf" required>
            </div>
            <button type="submit">Cadastrar</button>
        </form>
        <p><a href="login.php">Já tem conta? Faça login</a></p>
    </div>
</body>
</html>