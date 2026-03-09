<?php
session_start();
require_once '../../api/require.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nome = $_SESSION['user_nome'];
$user_nivel = $_SESSION['user_nivel'];

// Auxiliar vê todas as turmas (assim como Gerente). Filtro por turma fica na interface.
$result = mysqli_query($conn, "SELECT t.*, c.nome as curso_nome FROM turmas t 
                     JOIN cursos c ON t.curso_id = c.id");
$turmas = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Turmas - SENAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body style="background: #f1f5f9; color: #334155;">
    <div class="dashboard-layout">
        <aside class="sidebar">
            <a href="dashboard.php" class="sidebar-logo">SENAI</a>
            <nav class="sidebar-nav">
                <li class="nav-item">
                    <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                </li>
                <?php if ($user_nivel === 'Administrador'): ?>
                <li class="nav-item">
                    <a href="usuarios.php"><i class="fa-solid fa-users-gear"></i> Usuários</a>
                </li>
                <li class="nav-item">
                    <a href="importacao.php"><i class="fa-solid fa-file-import"></i> Importar Dados</a>
                </li>
                <?php
endif; ?>
                <li class="nav-item active">
                    <a href="turmas.php"><i class="fa-solid fa-users-rectangle"></i> Turmas</a>
                </li>
            </nav>
            <div class="sidebar-footer" style="padding: 2rem;">
                <li class="nav-item" style="list-style:none;">
                    <a href="../../api/logout.php" style="background: rgba(0,0,0,0.2); border-radius: 8px;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
                </li>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="user-info">
                    <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
                    <div>
                        <span style="display:block; font-size: 0.8rem; color: #64748b;">Bem-vindo</span>
                        <strong style="font-weight: 700;"><?php echo $user_nome; ?></strong>
                    </div>
                </div>
            </header>

            <div class="page-header" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.8rem; font-weight: 700; color: #1e293b;">Minhas Turmas</h2>
                <p style="color: #64748b;">Visualização de turmas e cursos ativos.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
                <?php foreach ($turmas as $t): ?>
                <div class="stat-card blue" style="padding: 1rem; min-height: auto; align-items: center; text-align: center; display: flex; flex-direction: column; justify-content: center; border-radius: 8px;">
                    <div class="stat-icon" style="margin-bottom: 0.5rem; width: 40px; height: 40px; font-size: 1.2rem; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-info" style="margin-left: 0;">
                        <h3 style="font-size: 1rem; margin-bottom: 0.2rem;"><?php echo $t['nome']; ?></h3>
                        <p style="font-size: 0.75rem;"><?php echo $t['curso_nome']; ?></p>
                    </div>
                </div>
                <?php
endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
