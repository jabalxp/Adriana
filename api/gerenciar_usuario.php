<?php
session_start();
require_once 'require.php';

// Check if user is logged in and is an Administrator
if (!isset($_SESSION['user_id']) || $_SESSION['user_nivel'] !== 'Administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id_usuario = filter_var($_POST['id_usuario'], FILTER_VALIDATE_INT);

    if (!$id_usuario) {
        echo json_encode(['success' => false, 'message' => 'ID de usuário inválido.']);
        exit();
    }

    switch ($action) {
        case 'change_password':
            $nova_senha = $_POST['nova_senha'] ?? '';
            if (strlen($nova_senha) < 6) {
                echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres.']);
                exit();
            }
            $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE usuarios SET senha = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $hash, $id_usuario);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso!']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Erro ao alterar senha.']);
            }
            break;

        case 'reset_password':
            // Senha padrão definida no plano: Senai@SP
            $senha_padrao = 'Senai@SP';
            $hash = password_hash($senha_padrao, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE usuarios SET senha = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $hash, $id_usuario);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Senha resetada para Senai@SP com sucesso!']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Erro ao resetar senha.']);
            }
            break;

        case 'change_email':
            $novo_email = filter_var($_POST['novo_email'], FILTER_VALIDATE_EMAIL);
            if (!$novo_email) {
                echo json_encode(['success' => false, 'message' => 'E-mail inválido.']);
                exit();
            }

            // Verificar se o e-mail já existe
            $stmt_check = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE email = ? AND id != ?");
            mysqli_stmt_bind_param($stmt_check, "si", $novo_email, $id_usuario);
            mysqli_stmt_execute($stmt_check);
            if (mysqli_num_rows(mysqli_stmt_get_result($stmt_check)) > 0) {
                echo json_encode(['success' => false, 'message' => 'Este e-mail já está em uso.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "UPDATE usuarios SET email = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $novo_email, $id_usuario);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'E-mail alterado com sucesso!']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Erro ao alterar e-mail.']);
            }
            break;

        case 'add_user':
            $nome = $_POST['nome'] ?? '';
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $nivel = $_POST['nivel'] ?? 'Auxiliar';
            $senha = $_POST['senha'] ?? '';

            if (!$nome || !$email || strlen($senha) < 6) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos ou senha muito curta.']);
                exit();
            }

            // Validar nível
            if (!in_array($nivel, ['Administrador', 'Gerente', 'Auxiliar'])) {
                echo json_encode(['success' => false, 'message' => 'Nível de acesso inválido.']);
                exit();
            }

            // Verificar se o e-mail já existe
            $stmt_check = mysqli_prepare($conn, "SELECT id FROM usuarios WHERE email = ?");
            mysqli_stmt_bind_param($stmt_check, "s", $email);
            mysqli_stmt_execute($stmt_check);
            if (mysqli_num_rows(mysqli_stmt_get_result($stmt_check)) > 0) {
                echo json_encode(['success' => false, 'message' => 'Este e-mail já está em uso.']);
                exit();
            }

            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO usuarios (nome, email, senha, nivel) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $nome, $email, $hash, $nivel);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Usuário cadastrado com sucesso!']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar usuário.']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
            break;
    }
}
else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
}
?>
