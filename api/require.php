<?php
$host = 'localhost';
$dbname = 'sistema_atestados';
$username = 'root';
$password = '';
$port = 3308;

$conn = mysqli_connect($host, $username, $password, $dbname, $port);

if (!$conn) {
    die("Erro ao conectar com o banco de dados: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Normalizar nível de usuário para evitar problemas de case-sensitivity ou espaços
if (isset($_SESSION['user_nivel'])) {
    $_SESSION['user_nivel'] = trim(ucfirst(strtolower($_SESSION['user_nivel'])));
}
?>
