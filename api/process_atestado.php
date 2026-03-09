<?php
require_once 'require.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_pessoal = $_POST['email_pessoal'] ?? '';
    $nome_aluno_novo = $_POST['nome_aluno_novo'] ?? '';
    $aluno_id_post = $_POST['aluno_id'] ?? '';
    $turma_id = $_POST['turma_id'] ?? '';
    $tipo = $_POST['tipo_documento'] ?? '';

    // Campos opcionais
    $data_inicio = !empty($_POST['data_inicio']) ? $_POST['data_inicio'] : null;
    $data_fim = !empty($_POST['data_fim']) ? $_POST['data_fim'] : null;
    $cid_motivo = $_POST['cid_motivo'] ?? null;
    $codigo_judicial = $_POST['codigo_judicial'] ?? null;
    $outro_motivo = $_POST['outro_motivo'] ?? null;

    // 1. Verificar/Criar Aluno
    $nome_aluno_sucesso = "Aluno";

    if ($aluno_id_post === 'novo') {
        // Criar novo aluno
        $stmt = mysqli_prepare($conn, "INSERT INTO alunos (nome, email_pessoal, turma_id) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssi", $nome_aluno_novo, $email_pessoal, $turma_id);
        mysqli_stmt_execute($stmt);
        $aluno_id = mysqli_insert_id($conn);
        $nome_aluno_sucesso = $nome_aluno_novo;
    }
    else {
        // Aluno já existe e foi selecionado
        $aluno_id = $aluno_id_post;

        // Atualizar email se quiser, mas opcional. Vamos apenas buscar o nome para a tela de sucesso.
        $stmt = mysqli_prepare($conn, "SELECT nome FROM alunos WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $aluno_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $aluno = mysqli_fetch_assoc($result);
        if ($aluno) {
            $nome_aluno_sucesso = $aluno['nome'];
        }
    }

    // 2. Tratar Upload
    $anexo_path = "";
    if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/';
        $file_extension = pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('atestado_') . '.' . $file_extension;
        $anexo_path = 'assets/uploads/' . $file_name;

        move_uploaded_file($_FILES['anexo']['tmp_name'], $upload_dir . $file_name);
    }

    // 3. Salvar Atestado
    $sql = "INSERT INTO atestados (aluno_id, turma_id, tipo, data_inicio, data_fim, cid_motivo, codigo_judicial, outro_motivo_desc, anexo_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iisssssss", $aluno_id, $turma_id, $tipo, $data_inicio, $data_fim, $cid_motivo, $codigo_judicial, $outro_motivo, $anexo_path);
    mysqli_stmt_execute($stmt);

    // Simulação de sucesso
    echo "<!DOCTYPE html>
    <html lang='pt-br'>
    <head>
        <meta charset='UTF-8'>
        <link rel='stylesheet' href='../assets/css/style.css'>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
        <title>Sucesso!</title>
    </head>
    <body style='background: #f1f5f9; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif;'>
        <div class='content-card' style='text-align: center; max-width: 400px;'>
            <i class='fa-solid fa-circle-check' style='font-size: 5rem; color: #22c55e; margin-bottom: 2rem;'></i>
            <h2 style='margin-bottom: 1rem;'>Documento Enviado!</h2>
            <p style='color: #64748b; margin-bottom: 2rem;'>Obrigado, $nome_aluno_sucesso. Seu documento foi enviado para análise do Gerente.</p>
            <a href='../index.php' class='btn-primary' style='text-decoration: none;'>Voltar ao Início</a>
        </div>
    </body>
    </html>";
}
else {
    header("Location: ../index.php");
    exit();
}
