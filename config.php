<?php
$host = "localhost";
$dbname = "rafaelmodas";
$user = "root";
$password = "";


$strcon = mysqli_connect($host, $user, $password, $dbname);

// Check connection
if ($strcon->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}
?>
echo "Sucesso na Conexão";