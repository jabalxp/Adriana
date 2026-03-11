<?php
session_start();
require_once 'require.php';

// Verificar se tem permissão
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_nivel'], ['Administrador', 'Gerente', 'Auxiliar'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit();
}

// Receber dados JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['alunos']) || !is_array($data['alunos'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit();
}

try {
    mysqli_begin_transaction($conn);
    $count = 0;

    foreach ($data['alunos'] as $aluno_data) {
        $nome_aluno = trim($aluno_data['nome']);
        $nome_turma = trim($aluno_data['turma']);
        $nome_curso = $nome_turma; // Usando o nome da turma como nome do curso conforme solicitado
        $periodo = 'Integral'; // Padrão se não identificado

        // Mock de email se não houver (ou usar um padrão)
        $email_aluno = isset($aluno_data['email']) ? trim($aluno_data['email']) : strtolower(str_replace(' ', '.', $nome_aluno)) . '@aluno.senai.br';

        // 1. Curso
        // Garantir que o curso exista e obter o ID
        $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO cursos (nome) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "s", $nome_curso);
        mysqli_stmt_execute($stmt);

        $stmt = mysqli_prepare($conn, "SELECT id FROM cursos WHERE nome = ?");
        mysqli_stmt_bind_param($stmt, "s", $nome_curso);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $curso_id = $row['id'] ?? null;

        if (!$curso_id) {
            throw new Exception("Falha ao criar ou encontrar o curso: " . $nome_curso);
        }

        // 2. Turma
        // Verificar se a turma já existe para este curso
        $stmt = mysqli_prepare($conn, "SELECT id FROM turmas WHERE nome = ? AND curso_id = ?");
        mysqli_stmt_bind_param($stmt, "si", $nome_turma, $curso_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $turma_id = $row['id'] ?? null;

        if (!$turma_id) {
            $stmt = mysqli_prepare($conn, "INSERT INTO turmas (nome, curso_id, periodo) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sis", $nome_turma, $curso_id, $periodo);
            mysqli_stmt_execute($stmt);
            $turma_id = mysqli_insert_id($conn);
        }

        if (!$turma_id) {
            throw new Exception("Falha ao criar ou encontrar a turma: " . $nome_turma);
        }

        // 3. Aluno
        $stmt = mysqli_prepare($conn, "INSERT INTO alunos (nome, email_pessoal, turma_id) 
                                VALUES (?, ?, ?) 
                                ON DUPLICATE KEY UPDATE nome = VALUES(nome), turma_id = VALUES(turma_id)");
        mysqli_stmt_bind_param($stmt, "ssi", $nome_aluno, $email_aluno, $turma_id);
        mysqli_stmt_execute($stmt);

        $count++;
    }

    mysqli_commit($conn);
    echo json_encode(['success' => true, 'count' => $count]);

}
catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
