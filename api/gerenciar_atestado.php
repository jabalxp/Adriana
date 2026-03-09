<?php
session_start();
require_once 'require.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sessão expirada.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nivel = $_SESSION['user_nivel'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'get_atestados':
            $status = $_POST['status'] ?? '';

            $sql = "SELECT a.*, al.nome as aluno_nome, t.nome as turma_nome, c.nome as curso_nome 
                    FROM atestados a
                    JOIN alunos al ON a.aluno_id = al.id
                    JOIN turmas t ON a.turma_id = t.id
                    JOIN cursos c ON t.curso_id = c.id";

            $conditions = [];
            $params = [];
            $types = "";

            // Filtro por nível: Auxiliar só vê turmas que ele leciona (se houver essa relação forte)
            // No banco atual, docente_turmas vincula professor a turma.
            if ($user_nivel === 'Auxiliar') {
                $sql .= " JOIN docente_turmas dt ON t.id = dt.turma_id AND dt.usuario_id = ?";
                $params[] = $user_id;
                $types .= "i";
            }

            if (!empty($status)) {
                $conditions[] = "a.status = ?";
                $params[] = $status;
                $types .= "s";
            }

            if (count($conditions) > 0) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY a.created_at DESC";

            $stmt = mysqli_prepare($conn, $sql);
            if (count($params) > 0) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }

            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $atestados = mysqli_fetch_all($result, MYSQLI_ASSOC);

            echo json_encode(['success' => true, 'data' => $atestados]);
            break;

        case 'update_status':
            if ($user_nivel === 'Auxiliar') {
                echo json_encode(['success' => false, 'message' => 'Você não tem permissão para alterar o status.']);
                exit();
            }

            $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            $status = $_POST['status'] ?? '';

            if (!$id || !in_array($status, ['Aceito', 'Recusado', 'Pendente'])) {
                echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "UPDATE atestados SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $status, $id);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status.']);
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
