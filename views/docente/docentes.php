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

// Apenas Gerente acessa esta página
if ($user_nivel !== 'Gerente') {
    header("Location: dashboard.php");
    exit();
}

// Buscar todos os docentes
$result = mysqli_query($conn, "SELECT * FROM usuarios WHERE nivel = 'Auxiliar' ORDER BY nome ASC");
$docentes = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docentes - SENAI</title>
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
                <li class="nav-item active">
                    <a href="docentes.php"><i class="fa-solid fa-user-tie"></i> Docentes</a>
                </li>
                <li class="nav-item">
                    <a href="turmas.php"><i class="fa-solid fa-users-rectangle"></i> Minhas Turmas</a>
                </li>
                <li class="nav-item">
                    <a href="calendario.php"><i class="fa-solid fa-calendar-days"></i> Calendário</a>
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
                <h2 style="font-size: 1.8rem; font-weight: 700; color: #1e293b;">Docentes Cadastrados</h2>
                <p style="color: #64748b;">Listagem de professores do sistema.</p>
            </div>

            <div class="content-card">
                <table>
                    <thead>
                        <tr>
                            <th>NOME</th>
                            <th>E-MAIL</th>
                            <th>NÍVEL</th>
                            <th style="text-align: right;">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($docentes as $doc): ?>
                        <tr>
                            <td><strong><?php echo $doc['nome']; ?></strong></td>
                            <td><?php echo $doc['email']; ?></td>
                            <td><span class="badge aceito"><?php echo $doc['nivel']; ?></span></td>
                            <td style="text-align: right;">
                                <button class="btn-primary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.8rem;">Ver Perfil</button>
                            </td>
                        </tr>
                        <?php
endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
