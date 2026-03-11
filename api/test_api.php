<?php
// Simular uma requisição POST para gerenciar_atestado.php
$_SESSION['user_id'] = 1;
$_SESSION['user_nivel'] = 'Gerente';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'update_status';
$_POST['id'] = 1;
$_POST['status'] = 'Aceito';

// Mock do require.php para não falhar a conexão se não estiver no ambiente real
// Mas aqui vamos tentar incluir o original
try {
    include 'c:/xampp/htdocs/Adriana/api/gerenciar_atestado.php';
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
