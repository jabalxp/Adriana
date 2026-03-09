<?php
session_start();
require_once 'require.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

$user_nivel = $_SESSION['user_nivel'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Apenas Administrador e Gerente podem realizar ações de escrita em turmas
    if ($user_nivel === 'Auxiliar' && !in_array($action, ['get_turma', 'get_alunos', 'add_aluno'])) {
        echo json_encode(['success' => false, 'message' => 'Você não tem permissão para realizar esta ação.']);
        exit();
    }

    switch ($action) {
        case 'add_turma':
            $nome = $_POST['nome'] ?? '';
            $curso_id = filter_var($_POST['curso_id'], FILTER_VALIDATE_INT);
            // Periodo removido, usando valor padrão
            $periodo = 'Manhã';

            if (!$nome || !$curso_id) {
                echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO turmas (nome, curso_id, periodo) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sis", $nome, $curso_id, $periodo);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Turma adicionada com sucesso!']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Erro ao adicionar turma.']);
            }
            break;

        case 'edit_turma':
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $nome = $_POST['nome'] ?? '';
            $curso_id = filter_var($_POST['curso_id'], FILTER_VALIDATE_INT);
            // Periodo removido, mantendo o valor ou usando padrão
            $periodo = 'Manhã';

            if (!$id || !$nome || !$curso_id) {
                echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "UPDATE turmas SET nome = ?, curso_id = ?, periodo = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sisi", $nome, $curso_id, $periodo, $id);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Turma atualizada com sucesso!']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar turma.']);
            }
            break;

        case 'get_turma':
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "SELECT * FROM turmas WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $turma = mysqli_fetch_assoc($result);

            if ($turma) {
                echo json_encode(['success' => true, 'data' => $turma]);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Turma não encontrada.']);
            }
            break;

        case 'add_aluno':
            $turma_id = filter_var($_POST['turma_id'], FILTER_VALIDATE_INT);
            $nome = $_POST['nome'] ?? '';

            if (!$turma_id || !$nome) {
                echo json_encode(['success' => false, 'message' => 'Nome do aluno é obrigatório.']);
                exit();
            }

            // Gerar um único placeholder para o email (campo UNIQUE e NOT NULL no banco atual)
            $email = "aluno_" . time() . "_" . rand(1000, 9999) . "@sistema.local";

            $stmt = mysqli_prepare($conn, "INSERT INTO alunos (nome, email_pessoal, turma_id) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssi", $nome, $email, $turma_id);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Aluno cadastrado com sucesso!']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar aluno.']);
            }
            break;

        case 'get_alunos':
            $turma_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            if (!$turma_id) {
                echo json_encode(['success' => false, 'message' => 'ID de turma inválido.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "SELECT * FROM alunos WHERE turma_id = ? ORDER BY nome ASC");
            mysqli_stmt_bind_param($stmt, "i", $turma_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $alunos = mysqli_fetch_all($result, MYSQLI_ASSOC);

            echo json_encode(['success' => true, 'data' => $alunos]);
            break;

        case 'edit_aluno':
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $nome = $_POST['nome'] ?? '';

            if (!$id || !$nome) {
                echo json_encode(['success' => false, 'message' => 'ID e nome são obrigatórios.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "UPDATE alunos SET nome = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $nome, $id);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Nome do aluno atualizado!']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar aluno.']);
            }
            break;

        case 'delete_aluno':
            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "DELETE FROM alunos WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Aluno removido com sucesso!']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Erro ao remover aluno.']);
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
