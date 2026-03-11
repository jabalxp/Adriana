<?php
session_start();
require_once 'require.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_nivel'], ['Administrador', 'Gerente', 'Auxiliar'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($file, "r")) !== FALSE) {
        try {
            mysqli_begin_transaction($conn);

            $count = 0;
            // Pular primeira linha se houver cabeçalho (pode ser ajustado conforme necessidade)
            // fgetcsv($handle); 

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Formato: 0:nome_aluno, 1:email_aluno, 2:nome_turma, 3:nome_curso, 4:periodo
                if (count($data) < 4)
                    continue;

                $nome_aluno = trim($data[0]);
                $email_aluno = trim($data[1]);
                $nome_turma = trim($data[2]);
                $nome_curso = trim($data[3]);
                $periodo = isset($data[4]) ? trim($data[4]) : 'Manhã';

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

                // 2. Turma
                // Verificar se a turma já existe para este curso e período
                $stmt = mysqli_prepare($conn, "SELECT id FROM turmas WHERE nome = ? AND curso_id = ? AND periodo = ?");
                mysqli_stmt_bind_param($stmt, "sis", $nome_turma, $curso_id, $periodo);
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

                // 3. Aluno
                // Inserir ou atualizar aluno pelo email
                $stmt = mysqli_prepare($conn, "INSERT INTO alunos (nome, email_pessoal, turma_id) 
                                       VALUES (?, ?, ?) 
                                       ON DUPLICATE KEY UPDATE nome = VALUES(nome), turma_id = VALUES(turma_id)");
                mysqli_stmt_bind_param($stmt, "ssi", $nome_aluno, $email_aluno, $turma_id);
                mysqli_stmt_execute($stmt);

                $count++;
            }

            mysqli_commit($conn);
            fclose($handle);
            header("Location: ../views/docente/importacao.php?success=" . $count);
            exit();

        }
        catch (Exception $e) {
            mysqli_rollback($conn);
            fclose($handle);
            header("Location: ../views/docente/importacao.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }
    else {
        header("Location: ../views/docente/importacao.php?error=Não foi possível abrir o arquivo.");
        exit();
    }
}
else {
    header("Location: ../views/docente/importacao.php");
    exit();
}
