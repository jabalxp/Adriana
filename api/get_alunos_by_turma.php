<?php
require_once 'require.php';
header('Content-Type: application/json');

$turma_id = $_GET['turma_id'] ?? 0;

if ($turma_id) {
    // Buscar os alunos dessa turma ordenados por nome
    $stmt = mysqli_prepare($conn, "SELECT id, nome FROM alunos WHERE turma_id = ? ORDER BY nome ASC");
    mysqli_stmt_bind_param($stmt, "i", $turma_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $alunos = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo json_encode($alunos);
}
else {
    echo json_encode([]);
}
