<?php
session_start();
require_once '../../api/require.php';

// Segurança: Se não estiver logado, volta para o login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_nome = $_SESSION['user_nome'];
$user_nivel = $_SESSION['user_nivel'];

// Auxiliar vê TODAS as turmas e atestados (com filtro na interface)
// Gerente e Administrador também veem tudo
$result = mysqli_query($conn, "SELECT * FROM turmas");
$turmas = mysqli_fetch_all($result, MYSQLI_ASSOC);

$result = mysqli_query($conn, "SELECT a.*, al.nome as aluno_nome, t.nome as turma_nome 
                     FROM atestados a 
                     JOIN alunos al ON a.aluno_id = al.id
                     JOIN turmas t ON a.turma_id = t.id
                     ORDER BY a.created_at DESC");
$atestados = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Estatísticas detalhadas para Gerente e Administrador
$stats_detalhes = [];
if ($user_nivel === 'Gerente' || $user_nivel === 'Administrador') {
    // Turma com mais atestados
    $result = mysqli_query($conn, "SELECT t.nome, COUNT(a.id) as total FROM atestados a JOIN turmas t ON a.turma_id = t.id GROUP BY a.turma_id ORDER BY total DESC LIMIT 1");
    $stats_detalhes['turma_mais'] = mysqli_fetch_assoc($result);

    // Turma com menos atestados
    $result = mysqli_query($conn, "SELECT t.nome, COUNT(a.id) as total FROM atestados a JOIN turmas t ON a.turma_id = t.id GROUP BY a.turma_id ORDER BY total ASC LIMIT 1");
    $stats_detalhes['turma_menos'] = mysqli_fetch_assoc($result);

    // Aluno com mais atestados
    $result = mysqli_query($conn, "SELECT al.nome, COUNT(a.id) as total FROM atestados a JOIN alunos al ON a.aluno_id = al.id GROUP BY a.aluno_id ORDER BY total DESC LIMIT 1");
    $stats_detalhes['aluno_mais'] = mysqli_fetch_assoc($result);

    // Aluno com menos atestados
    $result = mysqli_query($conn, "SELECT al.nome, COUNT(a.id) as total FROM atestados a JOIN alunos al ON a.aluno_id = al.id GROUP BY a.aluno_id ORDER BY total ASC LIMIT 1");
    $stats_detalhes['aluno_menos'] = mysqli_fetch_assoc($result);
}

$total_auxiliares = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM usuarios WHERE nivel = 'Auxiliar'"))[0];
$total_turmas = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM turmas"))[0];
$total_cursos = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM cursos"))[0];
$total_alunos = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM alunos"))[0];
$total_atestados = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM atestados"))[0];
$aguardando_analise = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM atestados WHERE status = 'Pendente'"))[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo $user_nivel; ?> SENAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <style>
        /* Hide default DataTables sort icons since we use Font Awesome */
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
        <!-- Sidebar -->
        <aside class="sidebar">
            <a href="dashboard.php" class="sidebar-logo">SENAI</a>
            <nav class="sidebar-nav">
                <li class="nav-item active">
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

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <span style="display:block; font-size: 0.8rem; color: #64748b;">Bem-vindo, <strong><?php echo $user_nivel; ?></strong></span>
                        <strong style="font-weight: 700;"><?php echo $user_nome; ?></strong>
                    </div>
                </div>
                
                <div class="status-indicator">
                    <div class="dot"></div>
                    <strong style="color: #0f172a;">Sistema Online</strong>
                    <div style="margin-left: 1.5rem; background: #f1f5f9; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600;">
                        <i class="fa-regular fa-calendar" style="margin-right: 0.5rem;"></i> <?php echo date('d/m/Y'); ?>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="page-header" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.8rem; font-weight: 700; color: #1e293b;">
                    <i class="fa-solid fa-gauge-high" style="color: #64748b; margin-right: 0.5rem;"></i>
                    Dashboard — Gestão de Atestados
                </h2>
                <p style="color: #64748b;">Olá <?php echo $user_nome; ?>, você está logado como <?php echo $user_nivel; ?>.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card red">
                    <div class="stat-icon"><i class="fa-solid fa-user-group"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_auxiliares; ?></h3>
                        <p>Auxiliares</p>
                    </div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_turmas; ?></h3>
                        <p>Turmas Ativas</p>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_alunos; ?></h3>
                        <p>Total Alunos</p>
                    </div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-icon"><i class="fa-solid fa-file-invoice"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $total_atestados; ?></h3>
                        <p>Atestados</p>
                    </div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $aguardando_analise; ?></h3>
                        <p>Pendentes</p>
                    </div>
                </div>
            </div>

            <?php if ($user_nivel === 'Gerente' || $user_nivel === 'Administrador'): ?>
            <!-- Advanced Stats Section -->
            <div class="page-header" style="margin: 2.5rem 0 1.5rem 0;">
                <h3 style="font-size: 1.4rem; font-weight: 700; color: #1e293b;">
                    <i class="fa-solid fa-chart-line" style="color: #ff3b3b; margin-right: 0.5rem;"></i>
                    Estatísticas de Gestão
                </h3>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card orange">
                    <div class="stat-icon"><i class="fa-solid fa-arrow-up-right-dots"></i></div>
                    <div class="stat-info">
                        <h3 style="font-size: 1.2rem;"><?php echo $stats_detalhes['turma_mais']['nome'] ?? 'N/A'; ?></h3>
                        <p>Turma com + Atestados (<?php echo $stats_detalhes['turma_mais']['total'] ?? 0; ?>)</p>
                    </div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fa-solid fa-arrow-down-short-wide"></i></div>
                    <div class="stat-info">
                        <h3 style="font-size: 1.2rem;"><?php echo $stats_detalhes['turma_menos']['nome'] ?? 'N/A'; ?></h3>
                        <p>Turma com - Atestados (<?php echo $stats_detalhes['turma_menos']['total'] ?? 0; ?>)</p>
                    </div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-icon"><i class="fa-solid fa-user-plus"></i></div>
                    <div class="stat-info">
                        <h3 style="font-size: 1.2rem; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?php echo $stats_detalhes['aluno_mais']['nome'] ?? 'N/A'; ?></h3>
                        <p>Aluno com + Atestados (<?php echo $stats_detalhes['aluno_mais']['total'] ?? 0; ?>)</p>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon"><i class="fa-solid fa-user-minus"></i></div>
                    <div class="stat-info">
                        <h3 style="font-size: 1.2rem; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;"><?php echo $stats_detalhes['aluno_menos']['nome'] ?? 'N/A'; ?></h3>
                        <p>Aluno com - Atestados (<?php echo $stats_detalhes['aluno_menos']['total'] ?? 0; ?>)</p>
                    </div>
                </div>
            </div>

            <!-- Stats by Turma Table -->
            <div class="content-card">
                <div class="card-header" style="margin-bottom: 1.5rem;">
                    <h4 style="font-weight: 700; color: #1e293b;">Destaques por Turma</h4>
                </div>
                <table id="tabela-destaques" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>TURMA</th>
                            <th>ALUNO COM MAIS ATESTADOS</th>
                            <th>SOMA</th>
                            <th>ALUNO COM MENOS ATESTADOS</th>
                            <th>SOMA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
    $result_turmas_all = mysqli_query($conn, "SELECT * FROM turmas");
    $turmas_all = mysqli_fetch_all($result_turmas_all, MYSQLI_ASSOC);
    foreach ($turmas_all as $t):
        // Aluno com mais na turma
        $stmt = mysqli_prepare($conn, "SELECT al.nome, COUNT(a.id) as total FROM atestados a JOIN alunos al ON a.aluno_id = al.id WHERE a.turma_id = ? GROUP BY a.aluno_id ORDER BY total DESC LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $t['id']);
        mysqli_stmt_execute($stmt);
        $mais = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        // Aluno com menos na turma
        $stmt = mysqli_prepare($conn, "SELECT al.nome, COUNT(a.id) as total FROM atestados a JOIN alunos al ON a.aluno_id = al.id WHERE a.turma_id = ? GROUP BY a.aluno_id ORDER BY total ASC LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $t['id']);
        mysqli_stmt_execute($stmt);
        $menos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>
                        <tr>
                            <td><strong><?php echo $t['nome']; ?></strong></td>
                            <td><?php echo $mais['nome'] ?? '---'; ?></td>
                            <td><span class="badge aceito"><?php echo $mais['total'] ?? 0; ?></span></td>
                            <td><?php echo $menos['nome'] ?? '---'; ?></td>
                            <td><span class="badge recusado"><?php echo $menos['total'] ?? 0; ?></span></td>
                        </tr>
                        <?php
    endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
endif; ?>

            <!-- Main Content Area -->
            <div class="content-card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fa-solid fa-file-waveform" style="color: #ff3b3b;"></i>
                        Atestados para Análise
                    </div>
                </div>
                
                <table id="tabela-atestados" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>ALUNO</th>
                            <th>TURMA</th>
                            <th>TIPO</th>
                            <th>STATUS</th>
                            <th style="text-align: right;">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($atestados as $at): ?>
                            <tr>
                                <td><strong><?php echo $at['aluno_nome']; ?></strong></td>
                                <td><?php echo $at['turma_nome']; ?></td>
                                <td><?php echo ucwords(str_replace('_', ' ', $at['tipo'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $at['status'] === 'Pendente' ? 'pendente' : ($at['status'] === 'Aceito' ? 'aceito' : 'recusado'); ?>">
                                        <?php echo $at['status']; ?>
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <?php if ($user_nivel === 'Gerente' && $at['status'] === 'Pendente'): ?>
                                        <a href="../../api/gerenciar_atestado.php?id=<?php echo (int)$at['id']; ?>&acao=aceitar" style="color: #22c55e; margin-right: 0.5rem;" title="Aceitar"><i class="fa-solid fa-check"></i></a>
                                        <a href="../../api/gerenciar_atestado.php?id=<?php echo (int)$at['id']; ?>&acao=recusar" style="color: #ef4444; margin-right: 0.5rem;" title="Recusar"><i class="fa-solid fa-xmark"></i></a>
                                    <?php
        elseif ($user_nivel === 'Auxiliar' && $at['status'] === 'Aceito' && !$at['professor_confirmou']): ?>
                                        <a href="../../api/gerenciar_atestado.php?id=<?php echo (int)$at['id']; ?>&acao=confirmar" style="color: #3b82f6; margin-right: 0.5rem;" title="Confirmar Recebimento"><i class="fa-solid fa-clipboard-check"></i></a>
                                    <?php
        endif; ?>
                                    
                                    <?php if ($at['professor_confirmou']): ?>
                                        <span style="color: #22c55e; margin-right: 0.5rem;" title="Professor Confirmou"><i class="fa-solid fa-check-double"></i></span>
                                    <?php
        endif; ?>

                                    <a href="../../<?php echo htmlspecialchars($at['anexo_path']); ?>" target="_blank" style="color: #64748b;" title="Ver Anexo"><i class="fa-solid fa-file-image"></i></a>
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
        var ptBr = {
            "sEmptyTable":     "Nenhum registro encontrado",
            "sInfo":           "Mostrando de _START_ até _END_ de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando 0 até 0 de 0 registros",
            "sInfoFiltered":   "(Filtrado de _MAX_ registros)",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sLoadingRecords": "Carregando...",
            "sProcessing":     "Processando...",
            "sSearch":         "Pesquisar:",
            "sZeroRecords":    "Nenhum registro encontrado",
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
        };

        <?php if ($user_nivel === 'Gerente' || $user_nivel === 'Administrador'): ?>
        $('#tabela-destaques').DataTable({
            language: ptBr,
            pageLength: 10,
            order: [[2, 'desc']],
            columnDefs: [{ orderable: false, targets: [] }]
        });
        <?php endif; ?>

        $('#tabela-atestados').DataTable({
            language: ptBr,
            pageLength: 10,
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: [4] }]
        });
    });
    </script>
</body>
</html>
