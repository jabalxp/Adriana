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

            <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="font-size: 1.8rem; font-weight: 700; color: #1e293b;">Minhas Turmas</h2>
                    <p style="color: #64748b;">Visualização de turmas e cursos ativos.</p>
                </div>
                <?php if ($user_nivel === 'Administrador' || $user_nivel === 'Gerente'): ?>
                <button class="btn-primary btn-add-turma" style="width: auto; padding: 0.8rem 1.5rem;"><i class="fa-solid fa-plus"></i> Adicionar Turma</button>
                <?php
endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem;">
                <?php foreach ($turmas as $t): ?>
                <div class="stat-card blue" style="padding: 1.5rem; min-height: auto; align-items: center; text-align: center; display: flex; flex-direction: column; justify-content: center; border-radius: 12px; transition: transform 0.2s;">
                    <div class="stat-icon" style="margin-bottom: 1rem; width: 48px; height: 48px; font-size: 1.5rem; display: flex; align-items: center; justify-content: center; background: #dbeafe; color: #1e40af;"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-info" style="margin-left: 0; margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.25rem; margin-bottom: 0.4rem; color: #1e293b;"><?php echo $t['nome']; ?></h3>
                        <p style="font-size: 0.85rem; color: #64748b;"><?php echo $t['curso_nome']; ?></p>
                    </div>
                    <?php if ($user_nivel === 'Administrador' || $user_nivel === 'Gerente'): ?>
                    <div style="display: flex; gap: 0.5rem; width: 100%;">
                        <button class="btn-action btn-view-turma" data-id="<?php echo $t['id']; ?>" data-nome="<?php echo $t['nome']; ?>" title="Ver Turma" style="flex: 1; height: 36px; font-size: 0.8rem; border-radius: 8px;"><i class="fa-solid fa-eye"></i> Ver</button>
                        <button class="btn-action btn-edit-turma" data-id="<?php echo $t['id']; ?>" title="Editar Turma" style="flex: 1; height: 36px; font-size: 0.8rem; border-radius: 8px;"><i class="fa-solid fa-pen-to-square"></i> Edt</button>
                    </div>
                    <?php
    else: ?>
                    <button class="btn-action btn-view-turma" data-id="<?php echo $t['id']; ?>" data-nome="<?php echo $t['nome']; ?>" title="Ver Turma" style="width: 100%; height: 36px; font-size: 0.8rem; border-radius: 8px;"><i class="fa-solid fa-eye"></i> Ver Detalhes</button>
                    <?php
    endif; ?>
                </div>
                <?php
endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Modais de Gerenciamento de Turmas -->
    <div id="modal-container" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        
        <!-- Modal Adicionar/Editar Turma -->
        <div id="modal-turma" class="content-card" style="max-width:500px; width:90%; display:none;">
            <h3 id="modal-title" style="margin-bottom:1.5rem;">Adicionar Nova Turma</h3>
            <form id="form-turma">
                <input type="hidden" name="id" id="turma-id">
                <input type="hidden" name="action" id="turma-action" value="add_turma">
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; color:#64748b;">Nome da Turma</label>
                    <input type="text" name="nome" id="turma-nome" class="form-control" placeholder="Ex: DS-2024-2" required style="color:#334155; border:1px solid #e2e8f0; background:#f8fafc;">
                </div>
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; color:#64748b;">Curso</label>
                    <select name="curso_id" id="turma-curso" class="form-control" required style="color:#334155; border:1px solid #e2e8f0; background:#f8fafc; padding-left: 1rem;">
                        <?php
$cursos_res = mysqli_query($conn, "SELECT * FROM cursos ORDER BY nome ASC");
while ($c = mysqli_fetch_assoc($cursos_res)) {
    echo "<option value='{$c['id']}'>{$c['nome']}</option>";
}
?>
                    </select>
                </div>
                <div style="display:flex; gap:1rem; margin-top:2rem;">
                    <button type="button" class="btn-secondary btn-close-modal" style="flex:1; padding:0.8rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer; background:white;">Cancelar</button>
                    <button type="submit" class="btn-primary" style="flex:1; padding:0.8rem;">Salvar</button>
                </div>
            </form>
        </div>

        <!-- Modal Visualizar Turma e Alunos -->
        <div id="modal-view-turma" class="content-card" style="max-width:600px; width:95%; display:none; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                <div>
                    <h3 id="view-turma-nome" style="margin-bottom: 0.2rem;">Detalhes da Turma</h3>
                    <p id="view-turma-curso" style="color: #64748b; font-size: 0.9rem;"></p>
                </div>
                <button type="button" class="btn-close-modal" style="background: none; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div style="background: #f8fafc; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid #e2e8f0;">
                <h4 style="font-size: 0.9rem; color: #1e293b; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-plus-circle" style="color: #ff3b3b;"></i> Adicionar Novo Aluno
                </h4>
                <form id="form-add-aluno" style="display: grid; grid-template-columns: 1fr auto; gap: 0.8rem; align-items: end;">
                    <input type="hidden" name="action" value="add_aluno">
                    <input type="hidden" name="turma_id" id="add-aluno-turma-id">
                    <div>
                        <label style="display:block; margin-bottom:0.4rem; font-size:0.75rem; color:#64748b; font-weight: 600;">NOME COMPLETO DO ALUNO</label>
                        <input type="text" name="nome" class="form-control" placeholder="Ex: João da Silva" required style="padding: 0.6rem 0.8rem; font-size: 0.85rem;">
                    </div>
                    <button type="submit" class="btn-primary" style="padding: 0.6rem 2rem; font-size: 0.85rem; height: 38px;"><i class="fa-solid fa-user-plus"></i> Adicionar</button>
                </form>
            </div>

            <h4 style="font-size: 1rem; color: #1e293b; margin-bottom: 1rem; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem;">Alunos Matriculados</h4>
            <div id="lista-alunos" style="display: grid; gap: 0.6rem;">
                <!-- Preenchido via JS -->
                <p style="text-align: center; color: #64748b; padding: 2rem;">Carregando alunos...</p>
            </div>
        </div>

        <!-- Modal Editar Aluno -->
        <div id="modal-edit-aluno" class="content-card" style="max-width:400px; width:90%; display:none;">
            <h3 style="margin-bottom:1.5rem;">Editar Aluno</h3>
            <form id="form-edit-aluno">
                <input type="hidden" name="id" id="edit-aluno-id">
                <input type="hidden" name="action" value="edit_aluno">
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; color:#64748b;">Nome Completo</label>
                    <input type="text" name="nome" id="edit-aluno-nome" class="form-control" required style="color:#334155; border:1px solid #e2e8f0; background:#f8fafc;">
                </div>
                <div style="display:flex; gap:1rem; margin-top:2rem;">
                    <button type="button" class="btn-secondary btn-close-modal-edit" style="flex:1; padding:0.8rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer; background:white;">Cancelar</button>
                    <button type="submit" class="btn-primary" style="flex:1; padding:0.8rem;">Salvar Alteração</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            background: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-action:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
        }
        .btn-edit-turma:hover {
            color: #ff3b3b;
            border-color: #ff3b3b;
        }
        .btn-view-turma:hover {
            color: #3b82f6;
            border-color: #3b82f6;
        }
        .aluno-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 1rem;
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .aluno-item:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
            transform: translateX(4px);
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(document).ready(function() {
        function openModal(modalId) {
            $('#modal-container').css('display', 'flex');
            $('.content-card', '#modal-container').hide();
            $(modalId).show();
        }

        function closeModal() {
            $('#modal-container').hide();
            $('#form-turma').trigger('reset');
            $('#form-add-aluno').trigger('reset');
            $('#form-edit-aluno').trigger('reset');
            $('#turma-id').val('');
        }

        $('.btn-close-modal').click(closeModal);
        $('.btn-close-modal-edit').click(function() {
            $('#modal-edit-aluno').hide();
            $('#modal-view-turma').show();
        });

        $('.btn-add-turma').click(function() {
            $('#modal-title').text('Adicionar Nova Turma');
            $('#turma-action').val('add_turma');
            openModal('#modal-turma');
        });

        $('.btn-edit-turma').click(function() {
            const id = $(this).data('id');
            $.post('../../api/gerenciar_turma.php', { action: 'get_turma', id: id }, function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    $('#modal-title').text('Editar Turma');
                    $('#turma-action').val('edit_turma');
                    $('#turma-id').val(res.data.id);
                    $('#turma-nome').val(res.data.nome);
                    $('#turma-curso').val(res.data.curso_id);
                    openModal('#modal-turma');
                } else {
                    alert(res.message);
                }
            });
        });

        $('.btn-view-turma').click(function() {
            const id = $(this).data('id');
            const nome = $(this).data('nome');
            const curso = $(this).closest('.stat-card').find('.stat-info p').text();
            
            $('#view-turma-nome').text(nome);
            $('#view-turma-curso').text(curso);
            $('#add-aluno-turma-id').val(id);
            
            carregarAlunos(id);
            openModal('#modal-view-turma');
        });

        function carregarAlunos(id) {
            $('#lista-alunos').html('<p style="text-align: center; color: #64748b; padding: 1rem;">Carregando alunos...</p>');
            $.post('../../api/gerenciar_turma.php', { action: 'get_alunos', id: id }, function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    let html = '';
                    if (res.data.length === 0) {
                        html = '<p style="text-align: center; color: #94a3b8; padding: 2rem; background: #f8fafc; border-radius: 8px; border: 2px dashed #e2e8f0;">Nenhum aluno cadastrado nesta turma.</p>';
                    } else {
                        res.data.forEach(aluno => {
                            html += `
                                <div class="aluno-item">
                                    <div style="flex: 1;">
                                        <strong style="display:block; color:#1e293b;">${aluno.nome}</strong>
                                    </div>
                                    <div style="display: flex; gap: 0.8rem; align-items: center;">
                                        <button class="btn-edit-aluno" data-id="${aluno.id}" data-nome="${aluno.nome}" title="Editar Nome" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:0.9rem;"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn-delete-aluno" data-id="${aluno.id}" title="Remover Aluno" style="background:none; border:none; color:#f87171; cursor:pointer; font-size:0.9rem;"><i class="fa-solid fa-trash"></i></button>
                                        <i class="fa-solid fa-user-graduate" style="color: #cbd5e1; margin-left: 0.5rem;"></i>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    $('#lista-alunos').html(html);
                } else {
                    $('#lista-alunos').html(`<p style="color: #ef4444; text-align: center;">Erro: ${res.message}</p>`);
                }
            });
        }

        // Delegated events for student actions
        $(document).on('click', '.btn-edit-aluno', function() {
            const id = $(this).data('id');
            const nome = $(this).data('nome');
            
            $('#edit-aluno-id').val(id);
            $('#edit-aluno-nome').val(nome);
            
            $('#modal-view-turma').hide();
            $('#modal-edit-aluno').show();
        });

        $('#form-edit-aluno').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.post('../../api/gerenciar_turma.php', formData, function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    $('#modal-edit-aluno').hide();
                    $('#modal-view-turma').show();
                    carregarAlunos($('#add-aluno-turma-id').val());
                } else {
                    alert(res.message);
                }
            });
        });

        $(document).on('click', '.btn-delete-aluno', function() {
            if (confirm('Tem certeza que deseja remover este aluno da turma?')) {
                const id = $(this).data('id');
                $.post('../../api/gerenciar_turma.php', { action: 'delete_aluno', id: id }, function(response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        carregarAlunos($('#add-aluno-turma-id').val());
                    } else {
                        alert(res.message);
                    }
                });
            }
        });

        $('#form-add-aluno').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const turmaId = $('#add-aluno-turma-id').val();
            
            $.post('../../api/gerenciar_turma.php', formData, function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    $('#form-add-aluno').trigger('reset');
                    carregarAlunos(turmaId);
                } else {
                    alert(res.message);
                }
            });
        });

        $('#form-turma').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.post('../../api/gerenciar_turma.php', formData, function(response) {
                const res = JSON.parse(response);
                alert(res.message);
                if (res.success) {
                    closeModal();
                    location.reload();
                }
            });
        });

        // Fechar modal ao clicar fora
        $('#modal-container').click(function(e) {
            if ($(e.target).is('#modal-container')) {
                closeModal();
            }
        });
    });
    </script>
</body>
</html>
