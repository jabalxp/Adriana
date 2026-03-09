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

// Apenas Gerente e Administrador acessam esta página
if ($user_nivel === 'Auxiliar') {
    header("Location: dashboard.php");
    exit();
}

// Estatísticas detalhadas
$stats_detalhes = [];
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

// --- DADOS PARA OS GRÁFICOS ---

// 1. Atestados por Status
$result = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM atestados GROUP BY status");
$chart_status = ['labels' => [], 'data' => []];
while ($row = mysqli_fetch_assoc($result)) {
    $chart_status['labels'][] = $row['status'];
    $chart_status['data'][] = (int)$row['total'];
}

// 2. Atestados por Turma
$result = mysqli_query($conn, "SELECT t.nome, COUNT(a.id) as total FROM atestados a JOIN turmas t ON a.turma_id = t.id GROUP BY a.turma_id ORDER BY total DESC LIMIT 8");
$chart_turmas = ['labels' => [], 'data' => []];
while ($row = mysqli_fetch_assoc($result)) {
    $chart_turmas['labels'][] = $row['nome'];
    $chart_turmas['data'][] = (int)$row['total'];
}

// 3. Evolução Mensal (Últimos 6 meses)
$result = mysqli_query($conn, "SELECT DATE_FORMAT(created_at, '%b/%y') as mes, COUNT(*) as total FROM atestados WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY mes ORDER BY created_at ASC");
$chart_mensal = ['labels' => [], 'data' => []];
while ($row = mysqli_fetch_assoc($result)) {
    $chart_mensal['labels'][] = $row['mes'];
    $chart_mensal['data'][] = (int)$row['total'];
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes Estatísticos - SENAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        table.dataTable thead .sorting::before,
        table.dataTable thead .sorting_asc::before,
        table.dataTable thead .sorting_desc::before { display: none !important; }
        table.dataTable thead .sorting::after,
        table.dataTable thead .sorting_asc::after,
        table.dataTable thead .sorting_desc::after { font-family: 'Font Awesome 6 Free'; }
        
        .badge.aceito { background: #dcfce7; color: #166534; font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 20px; font-weight: 600; }
        .badge.recusado { background: #fee2e2; color: #991b1b; font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 20px; font-weight: 600; }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .charts-container { grid-template-columns: 1fr; }
        }

        .chart-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body style="background: #f1f5f9; color: #334155;">
    <div class="dashboard-layout">
        <!-- Sidebar -->
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
                <li class="nav-item">
                    <a href="turmas.php"><i class="fa-solid fa-users-rectangle"></i> Turmas</a>
                </li>
                <li class="nav-item">
                    <a href="atestado.php"><i class="fa-solid fa-clipboard-check"></i> Atestados</a>
                </li>
                <li class="nav-item active">
                    <a href="detalhes.php"><i class="fa-solid fa-chart-pie"></i> Detalhes</a>
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
            <header class="top-bar">
                <div class="user-info">
                    <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
                    <div>
                        <span style="display:block; font-size: 0.8rem; color: #64748b;">Estatísticas de Gestão</span>
                        <strong style="font-weight: 700;"><?php echo $user_nome; ?></strong>
                    </div>
                </div>
            </header>

            <div class="page-header" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.8rem; font-weight: 900; color: #1e293b;">Detalhes Estatísticos</h2>
                <p style="color: #64748b;">Visão aprofundada da gestão de atestados e turmas.</p>
            </div>

            <!-- Advanced Stats Section -->
            <div class="page-header" style="margin: 0 0 1rem 0;">
                <h3 style="font-size: 1.2rem; font-weight: 700; color: #1e293b;">
                    <i class="fa-solid fa-chart-line" style="color: #ff3b3b; margin-right: 0.5rem;"></i>
                    Resumo de Gestão
                </h3>
            </div>
            
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="stat-card orange">
                    <div class="stat-icon"><i class="fa-solid fa-arrow-up-right-dots"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats_detalhes['turma_mais']['total'] ?? 0; ?></h3>
                        <p>Atestados na Turma: <strong><?php echo $stats_detalhes['turma_mais']['nome'] ?? 'N/A'; ?></strong></p>
                    </div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fa-solid fa-arrow-down-short-wide"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats_detalhes['turma_menos']['total'] ?? 0; ?></h3>
                        <p>Atestados na Turma: <strong><?php echo $stats_detalhes['turma_menos']['nome'] ?? 'N/A'; ?></strong></p>
                    </div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-icon"><i class="fa-solid fa-user-plus"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats_detalhes['aluno_mais']['total'] ?? 0; ?></h3>
                        <p>Aluno com + Atestados: <strong><?php echo $stats_detalhes['aluno_mais']['nome'] ?? 'N/A'; ?></strong></p>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon"><i class="fa-solid fa-user-minus"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats_detalhes['aluno_menos']['total'] ?? 0; ?></h3>
                        <p>Aluno com - Atestados: <strong><?php echo $stats_detalhes['aluno_menos']['nome'] ?? 'N/A'; ?></strong></p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-container">
                <div class="chart-card">
                    <h4 style="margin-bottom: 1rem; color: #1e293b; font-size: 0.9rem; font-weight: 700;">Distribuição por Status</h4>
                    <div style="height: 250px;"><canvas id="chartStatus"></canvas></div>
                </div>
                <div class="chart-card">
                    <h4 style="margin-bottom: 1rem; color: #1e293b; font-size: 0.9rem; font-weight: 700;">Volume por Turma (Top 8)</h4>
                    <div style="height: 250px;"><canvas id="chartTurmas"></canvas></div>
                </div>
            </div>

            <div class="chart-card" style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 1rem; color: #1e293b; font-size: 0.9rem; font-weight: 700;">Evolução Semestral</h4>
                <div style="height: 300px;"><canvas id="chartMensal"></canvas></div>
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
while ($t = mysqli_fetch_assoc($result_turmas_all)):
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
endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#tabela-destaques').DataTable({
            language: {
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
                }
            },
            pageLength: 10,
            order: [[2, 'desc']]
        });

        // Configuração dos Gráficos
        const ctxStatus = document.getElementById('chartStatus');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($chart_status['labels']); ?>,
                datasets: [{
                    data: <?php echo json_encode($chart_status['data']); ?>,
                    backgroundColor: ['#eab308', '#22c55e', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 20, font: { family: 'Inter', size: 12 } } }
                },
                cutout: '70%'
            }
        });

        const ctxTurmas = document.getElementById('chartTurmas');
        new Chart(ctxTurmas, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_turmas['labels']); ?>,
                datasets: [{
                    label: 'Atestados',
                    data: <?php echo json_encode($chart_turmas['data']); ?>,
                    backgroundColor: '#3b82f6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } },
                    x: { grid: { display: false } }
                }
            }
        });

        const ctxMensal = document.getElementById('chartMensal');
        new Chart(ctxMensal, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_mensal['labels']); ?>,
                datasets: [{
                    label: 'Envios',
                    data: <?php echo json_encode($chart_mensal['data']); ?>,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#8b5cf6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
    </script>
</body>
</html>
