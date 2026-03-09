<?php
session_start();
require_once 'require.php';

// Verificar se é Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_nivel'] !== 'Administrador') {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];

    // Validar extensão
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'xlsx') {
        header("Location: ../views/docente/importacao.php?error=Apenas arquivos .xlsx são permitidos");
        exit();
    }

    $uploadDir = '../uploads/temp/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $tempFile = $uploadDir . uniqid() . '.xlsx';

    if (move_uploaded_file($file['tmp_name'], $tempFile)) {
        // Chamar script Python para converter Excel em JSON
        $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'excel_to_json.py';
        $tempFileAbs = realpath($tempFile);
        $command = "python " . escapeshellarg($scriptPath) . " " . escapeshellarg($tempFileAbs) . " 2>&1";
        $output = shell_exec($command);

        // Remover o arquivo temporário
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }

        $data = json_decode($output, true);

        if (!$data || isset($data['error'])) {
            $errorMsg = $data['error'] ?? "Erro ao processar o arquivo Excel. Saída: " . substr($output ?? '(vazio)', 0, 200);
            header("Location: ../views/docente/importacao.php?error=" . urlencode($errorMsg));
            exit();
        }

        if (empty($data['alunos'])) {
            header("Location: ../views/docente/importacao.php?error=Nenhum dado válido encontrado no Excel");
            exit();
        }

        try {
            mysqli_begin_transaction($conn);
            $count = 0;

            foreach ($data['alunos'] as $aluno_data) {
                $nome_aluno = trim($aluno_data['nome']);
                $nome_turma = trim($aluno_data['turma']);
                $nome_curso = $nome_turma; // Usando o nome da turma como curso 
                $periodo = 'Integral';
                $email_aluno = strtolower(str_replace(' ', '.', $nome_aluno)) . '@aluno.senai.br';

                // 1. Curso
                $stmt = mysqli_prepare($conn, "INSERT IGNORE INTO cursos (nome) VALUES (?)");
                mysqli_stmt_bind_param($stmt, "s", $nome_curso);
                mysqli_stmt_execute($stmt);

                $stmt = mysqli_prepare($conn, "SELECT id FROM cursos WHERE nome = ?");
                mysqli_stmt_bind_param($stmt, "s", $nome_curso);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $curso_id = $row['id'] ?? null;

                if (!$curso_id)
                    continue;

                // 2. Turma
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

                if (!$turma_id)
                    continue;

                // 3. Aluno
                $stmt = mysqli_prepare($conn, "INSERT INTO alunos (nome, email_pessoal, turma_id) 
                                        VALUES (?, ?, ?) 
                                        ON DUPLICATE KEY UPDATE nome = VALUES(nome), turma_id = VALUES(turma_id)");
                mysqli_stmt_bind_param($stmt, "ssi", $nome_aluno, $email_aluno, $turma_id);
                mysqli_stmt_execute($stmt);

                $count++;
            }

            mysqli_commit($conn);
            header("Location: ../views/docente/importacao.php?success=" . $count);
            exit();

        }
        catch (Exception $e) {
            mysqli_rollback($conn);
            header("Location: ../views/docente/importacao.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }
    else {
        header("Location: ../views/docente/importacao.php?error=Falha no upload do arquivo");
        exit();
    }
}
?>
