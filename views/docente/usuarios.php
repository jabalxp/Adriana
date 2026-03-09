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

// Apenas Administrador acessa esta página
if ($user_nivel !== 'Administrador') {
    header("Location: dashboard.php");
    exit();
}

// Buscar todos os usuários
$result = mysqli_query($conn, "SELECT * FROM usuarios ORDER BY nivel ASC, nome ASC");
$usuarios = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - SENAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <style>
        table.dataTable thead .sorting::before,
        table.dataTable thead .sorting_asc::before,
        table.dataTable thead .sorting_desc::before { display: none !important; }
        table.dataTable thead .sorting::after,
        table.dataTable thead .sorting_asc::after,
        table.dataTable thead .sorting_desc::after { font-family: 'Font Awesome 6 Free'; }
    </style>
</head>
<body style="background: #f1f5f9; color: #334155;">
    <div class="dashboard-layout">
        <aside class="sidebar">
            <a href="dashboard.php" class="sidebar-logo">SENAI</a>
            <nav class="sidebar-nav">
                <li class="nav-item">
                    <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="usuarios.php"><i class="fa-solid fa-users-gear"></i> Usuários</a>
                </li>
                <li class="nav-item">
                    <a href="importacao.php"><i class="fa-solid fa-file-import"></i> Importar Dados</a>
                </li>
                <li class="nav-item">
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
                        <span style="display:block; font-size: 0.8rem; color: #64748b;">Adm. Central</span>
                        <strong style="font-weight: 700;"><?php echo $user_nome; ?></strong>
                    </div>
                </div>
            </header>

            <div class="page-header" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.8rem; font-weight: 700; color: #1e293b;">Gestão de Usuários</h2>
                <p style="color: #64748b;">Administre as permissões de acesso do sistema.</p>
            </div>

            <div class="content-card">
                <table id="tabela-usuarios" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>NOME</th>
                            <th>E-MAIL</th>
                            <th>NÍVEL</th>
                            <th style="text-align: right;">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $user): ?>
                        <tr>
                            <td><strong><?php echo $user['nome']; ?></strong></td>
                            <td><?php echo $user['email']; ?></td>
                            <td>
                                <span class="badge <?php
    echo($user['nivel'] === 'Administrador' ? 'aceito' :
        ($user['nivel'] === 'Gerente' ? 'pendente' : 'blue'));
?>">
                                    <?php echo $user['nivel']; ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button class="btn-primary" style="width: auto; padding: 0.5rem 1rem; font-size: 0.8rem;">Editar</button>
                            </td>
                        </tr>
                        <?php
endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#tabela-usuarios').DataTable({
            language: {
                "sEmptyTable":     "Nenhum usuário encontrado",
                "sInfo":           "Mostrando de _START_ até _END_ de _TOTAL_ usuários",
                "sInfoEmpty":      "Mostrando 0 até 0 de 0 usuários",
                "sInfoFiltered":   "(Filtrado de _MAX_ registros)",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sLoadingRecords": "Carregando...",
                "sProcessing":     "Processando...",
                "sSearch":         "Pesquisar:",
                "sZeroRecords":    "Nenhum usuário encontrado",
                "oPaginate": {
                    "sFirst":    "Primeiro",
                    "sLast":     "Último",
                    "sNext":     "Próximo",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Ordenar colunas de forma ascendente",
                    "sSortDescending": ": Ordenar colunas de forma descendente"
                }
            },
            pageLength: 10,
            order: [[2, 'asc'], [0, 'asc']],
            columnDefs: [{ orderable: false, targets: [3] }]
        });
    });
    </script>
</body>
</html>
