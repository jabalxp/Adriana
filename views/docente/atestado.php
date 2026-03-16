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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análise de Atestados - SENAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-pendente { background: #fef9c3; color: #854d0e; }
        .status-aceito { background: #dcfce7; color: #166534; }
        .status-recusado { background: #fee2e2; color: #991b1b; }
        
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 1.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        
        th {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
        }
        
        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
            color: #334155;
        }

        tr:last-child td { border-bottom: none; }
        
        .btn-status {
            padding: 0.4rem;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: white;
            cursor: pointer;
            transition: all 0.2s;
            color: #64748b;
        }
        
        .btn-status:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        
        .btn-accept:hover { color: #22c55e; border-color: #22c55e; }
        .btn-reject:hover { color: #ef4444; border-color: #ef4444; }
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
                <?php if ($user_nivel === 'Administrador'): ?>
                <li class="nav-item">
                    <a href="usuarios.php"><i class="fa-solid fa-users-gear"></i> Usuários</a>
                </li>
                <?php
endif; ?>
                <?php if ($user_nivel !== 'Auxiliar'): ?>
                <li class="nav-item">
                    <a href="docentes.php"><i class="fa-solid fa-user-tie"></i> Docentes</a>
                </li>
                <?php
endif; ?>
                <li class="nav-item">
                    <a href="turmas.php"><i class="fa-solid fa-users-rectangle"></i> Turmas</a>
                </li>
                <li class="nav-item active">
                    <a href="atestado.php"><i class="fa-solid fa-clipboard-check"></i> Atestados</a>
                </li>
                <?php if ($user_nivel !== 'Auxiliar'): ?>
                <li class="nav-item">
                    <a href="detalhes.php"><i class="fa-solid fa-chart-pie"></i> Detalhes</a>
                </li>
                <li class="nav-item">
                    <a href="importacao.php"><i class="fa-solid fa-file-import"></i> Importar Excel</a>
                </li>
                <?php
endif; ?>
            </nav>
            <div class="sidebar-footer" style="padding: 2rem;">
                <li class="nav-item" style="list-style:none;">
                    <a href="../../api/logout.php" style="background: rgba(0,0,0,0.2); border-radius: 8px;"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
                </li>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <button id="sidebar-toggle" style="display: none; background: #ff3b3b; color: white; border: none; padding: 0.5rem; border-radius: 4px; cursor: pointer; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-bars"></i> Menu
                </button>
                <div class="user-info">
                    <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
                    <div>
                        <span style="display:block; font-size: 0.8rem; color: #64748b;">Bem-vindo, <strong><?php echo $user_nivel; ?></strong></span>
                        <strong style="font-weight: 700;"><?php echo $user_nome; ?></strong>
                    </div>
                </div>
            </header>

            <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 style="font-size: 1.8rem; font-weight: 900; color: #1e293b;">Análise de Atestados</h1>
                    <p style="color: #64748b;">Gerencie e analise os documentos enviados pelos alunos.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <select id="filter-status" class="form-control" style="width: auto; min-width: 150px;">
                        <option value="">Todos os Status</option>
                        <option value="Pendente">Pendentes</option>
                        <option value="Aceito">Aceitos</option>
                        <option value="Recusado">Recusados</option>
                    </select>
                </div>
            </div>

            <div class="table-container">
                <table id="table-atestados">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Aluno</th>
                            <th>Turma</th>
                            <th>Tipo</th>
                            <th>CID / Motivo</th>
                            <th>Anexo</th>
                            <th>Status</th>
                            <th>Confirmação</th>
                            <?php if ($user_nivel !== 'Auxiliar'): ?>
                            <th>Ações</th>
                            <?php
endif; ?>
                        </tr>
                    </thead>
                    <tbody id="lista-atestados">
                        <!-- Preenchido via AJAX -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal para ver Anexo -->
    <div id="modal-container" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center; flex-direction: column;">
        <div style="width: 90%; max-width: 1000px; height: 80vh; background: white; border-radius: 12px; position: relative; overflow: hidden; display: flex; flex-direction: column;">
            <div style="padding: 1rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; background: #f8fafc;">
                <h3 id="modal-title" style="margin:0; font-size: 1.1rem; color: #1e293b;">Visualizar Anexo</h3>
                <div style="display: flex; gap: 0.8rem; align-items: center;">
                    <a id="btn-download-atestado" href="#" download class="btn-status" style="text-decoration: none; padding: 0.5rem 1rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem; background: #3b82f6; color: white; border: none;">
                        <i class="fa-solid fa-download"></i> Baixar
                    </a>
                    <a id="btn-full-view" href="#" target="_blank" class="btn-status" style="text-decoration: none; padding: 0.5rem 1rem; font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem; background: #64748b; color: white; border: none;">
                        <i class="fa-solid fa-up-right-from-square"></i> Ver Completo
                    </a>
                    <button class="btn-close-modal" style="background:none; border:none; font-size: 1.8rem; cursor:pointer; color: #64748b; margin-left: 1rem;">&times;</button>
                </div>
            </div>
            <div id="modal-content" style="flex: 1; overflow: auto; padding: 1rem; display: flex; align-items: center; justify-content: center;">
                <!-- Conteúdo do anexo (img ou iframe) -->
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(document).ready(function() {
        function carregarAtestados() {
            const status = $('#filter-status').val();
            const colSpan = '<?php echo $user_nivel; ?>' === 'Auxiliar' ? 8 : 9;
            $('#lista-atestados').html(`<tr><td colspan="${colSpan}" style="text-align:center; padding: 3rem;">Carregando atestados...</td></tr>`);
            
            $.ajax({
                url: '../../api/gerenciar_atestado.php',
                type: 'POST',
                data: { action: 'get_atestados', status: status },
                success: function(response) {
                    try {
                        const res = JSON.parse(response);
                        if (res.success) {
                            let html = '';
                            if (res.data.length === 0) {
                                html = `<tr><td colspan="${colSpan}" style="text-align:center; padding: 3rem; color: #64748b;">Nenhum documento encontrado para suas turmas.</td></tr>`;
                            } else {
                                res.data.forEach(a => {
                                    const createdAt = new Date(a.created_at);
                                    const dataFormatada = createdAt.toLocaleDateString('pt-BR') + ' ' + 
                                                         createdAt.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                                    const statusClass = 'status-' + (a.status || 'pendente').toLowerCase();
                                    const isAuxiliar = '<?php echo $user_nivel; ?>' === 'Auxiliar';
                                    
                                    const statusDisplay = (a.status === 'Recusado') 
                                        ? '<span style="color: #22c55e;"><i class="fa-solid fa-check-double"></i> Concluída</span>' 
                                        : (a.professor_confirmou == 1 
                                            ? '<span style="color: #22c55e;"><i class="fa-solid fa-check-double"></i> Recebido</span>' 
                                            : (isAuxiliar && a.status === 'Aceito'
                                                ? `<button class="btn-status btn-confirm-receipt" data-id="${a.id}" title="Confirmar Recebimento" style="color: #3b82f6; border-color: #3b82f6;">
                                                    <i class="fa-solid fa-clipboard-check"></i> Confirmar
                                                   </button>`
                                                : '<span style="color: #64748b;"><i class="fa-solid fa-clock"></i> Pendente</span>'));

                                    html += `
                                        <tr>
                                            <td>${dataFormatada}</td>
                                            <td><strong>${a.aluno_nome}</strong></td>
                                            <td>${a.curso_nome} - ${a.turma_nome}</td>
                                            <td>${(a.tipo || '').replace('_', ' ')}</td>
                                            <td><span title="CID ou Motivo">${a.cid_motivo || a.codigo_judicial || a.outro_motivo_desc || '---'}</span></td>
                                            <td>
                                                <button class="btn-status btn-view-attachment" data-path="${a.anexo_path}" title="Ver Anexo">
                                                    <i class="fa-solid fa-file-lines"></i> Ver
                                                </button>
                                            </td>
                                            <td><span class="status-badge ${statusClass}">${a.status}</span></td>
                                            <td>${statusDisplay}</td>
                                            ${!isAuxiliar ? `
                                            <td>
                                                <div style="display:flex; gap:0.5rem;">
                                                    <button class="btn-status btn-accept" data-id="${a.id}" title="Aceitar" ${a.status !== 'Pendente' ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}><i class="fa-solid fa-check"></i></button>
                                                    <button class="btn-status btn-reject" data-id="${a.id}" title="Recusar" ${a.status !== 'Pendente' ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}><i class="fa-solid fa-xmark"></i></button>
                                                </div>
                                            </td>
                                            ` : ''}
                                        </tr>
                                    `;
                                });
                            }
                            $('#lista-atestados').html(html);
                        } else {
                            $('#lista-atestados').html(`<tr><td colspan="${colSpan}" style="text-align:center; padding: 3rem; color: #ef4444;">Erro: ${res.message}</td></tr>`);
                        }
                    } catch (e) {
                        console.error('Erro ao processar JSON:', e, response);
                        $('#lista-atestados').html(`<tr><td colspan="${colSpan}" style="text-align:center; padding: 3rem; color: #ef4444;">Erro na resposta do servidor. Verifique o console.</td></tr>`);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição AJAX:', error);
                    $('#lista-atestados').html(`<tr><td colspan="${colSpan}" style="text-align:center; padding: 3rem; color: #ef4444;">Erro de conexão com o servidor.</td></tr>`);
                }
            });
        }

        carregarAtestados();

        $('#filter-status').change(carregarAtestados);

        $(document).on('click', '.btn-view-attachment', function() {
            const path = '../../' + $(this).data('path');
            const ext = path.split('.').pop().toLowerCase();
            let content = '';

            // Update header buttons
            $('#btn-download-atestado').attr('href', path);
            $('#btn-full-view').attr('href', path);

            if (ext === 'pdf') {
                content = `<iframe src="${path}" width="100%" height="100%" style="border:none;"></iframe>`;
            } else {
                content = `<img src="${path}" style="max-width:100%; max-height:100%; object-fit:contain;">`;
            }

            $('#modal-content').html(content);
            $('#modal-container').css('display', 'flex');
        });

        $('.btn-close-modal').click(function() {
            $('#modal-container').hide();
            $('#modal-content').empty();
        });

        $(document).on('click', '.btn-accept', function() {
            const id = $(this).data('id');
            if (confirm('Deseja aceitar este atestado?')) {
                updateStatus(id, 'Aceito');
            }
        });

        $(document).on('click', '.btn-reject', function() {
            const id = $(this).data('id');
            if (confirm('Deseja recusar este atestado?')) {
                updateStatus(id, 'Recusado');
            }
        });

        $(document).on('click', '.btn-confirm-receipt', function() {
            const id = $(this).data('id');
            if (confirm('Deseja confirmar que recebeu este atestado?')) {
                $.post('../../api/gerenciar_atestado.php', { action: 'confirmar_recebimento', id: id }, function(response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        carregarAtestados();
                    } else {
                        alert(res.message);
                    }
                });
            }
        });

        function updateStatus(id, status) {
            $.post('../../api/gerenciar_atestado.php', { action: 'update_status', id: id, status: status }, function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    carregarAtestados();
                } else {
                    alert(res.message);
                }
            });
        }

        // Fechar modal ao clicar fora
        $('#modal-container').click(function(e) {
            if ($(e.target).is('#modal-container')) {
                $('.btn-close-modal').click();
            }
        });

        // Toggle Sidebar Mobile
        $('#sidebar-toggle').on('click', function() {
            $('.sidebar').toggleClass('active');
        });

        if ($(window).width() <= 768) {
            $('#sidebar-toggle').show();
        }
        
        $(window).resize(function() {
            if ($(window).width() <= 768) {
                $('#sidebar-toggle').show();
            } else {
                $('#sidebar-toggle').hide();
                $('.sidebar').removeClass('active');
            }
        });
    });
    </script>
</body>
</html>
