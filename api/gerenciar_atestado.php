<?php
session_start();
require_once 'require.php';

if (!isset($_SESSION['user_id'])) {
    die("Acesso negado.");
}

$atestado_id = $_GET['id'] ?? '';
$acao = $_GET['acao'] ?? ''; // aceitar, recusar, confirmar
$nivel = $_SESSION['user_nivel'];

if ($acao === 'aceitar' && $nivel === 'Gerente') {
    $stmt = mysqli_prepare($conn, "UPDATE atestados SET status = 'Aceito' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $atestado_id);
    mysqli_stmt_execute($stmt);
}
elseif ($acao === 'recusar' && $nivel === 'Gerente') {
    $stmt = mysqli_prepare($conn, "UPDATE atestados SET status = 'Recusado' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $atestado_id);
    mysqli_stmt_execute($stmt);
}
elseif ($acao === 'confirmar' && $nivel === 'Auxiliar') {
    $stmt = mysqli_prepare($conn, "UPDATE atestados SET professor_confirmou = 1 WHERE id = ? AND status = 'Aceito'");
    mysqli_stmt_bind_param($stmt, "i", $atestado_id);
    mysqli_stmt_execute($stmt);
}

header("Location: ../views/docente/dashboard.php");
exit();
?>
