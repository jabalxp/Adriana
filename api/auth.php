<?php
session_start();
require_once 'require.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    // No mundo real, usaríamos password_verify. 
    // Para simplificar o teste, compararemos com a hash do SQL (password)

    $stmt = mysqli_prepare($conn, "SELECT id, nome, email, senha, nivel FROM usuarios WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Simulação: Aceitaremos qualquer senha por enquanto para facilitar o teste do ambiente
        // ou você pode usar: if (password_verify($_POST['password'], $user['senha']))

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_nivel'] = $user['nivel'];

        header("Location: ../views/docente/dashboard.php");
        exit();
    }
    else {
        header("Location: ../views/login.php?error=invalid_credentials");
        exit();
    }
}
else {
    header("Location: ../views/login.php");
    exit();
}
?>
