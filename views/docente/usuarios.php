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
                <li class="nav-item">
                    <a href="atestado.php"><i class="fa-solid fa-clipboard-check"></i> Atestados</a>
                </li>
                <?php if ($user_nivel !== 'Auxiliar'): ?>
                <li class="nav-item">
                    <a href="detalhes.php"><i class="fa-solid fa-chart-pie"></i> Detalhes</a>
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
                <div class="user-info">
                    <div class="user-avatar"><i class="fa-solid fa-user"></i></div>
                    <div>
                        <span style="display:block; font-size: 0.8rem; color: #64748b;">Adm. Central</span>
                        <strong style="font-weight: 700;"><?php echo $user_nome; ?></strong>
                    </div>
                </div>
            </header>

            <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="font-size: 1.8rem; font-weight: 700; color: #1e293b;">Gestão de Usuários</h2>
                    <p style="color: #64748b;">Administre as permissões de acesso do sistema.</p>
                </div>
                <button class="btn-primary btn-add-user" style="width: auto; padding: 0.8rem 1.5rem;"><i class="fa-solid fa-plus"></i> Novo Usuário</button>
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
                            <td style="text-align: right; display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <button class="btn-action btn-change-pass" data-id="<?php echo $user['id']; ?>" data-nome="<?php echo $user['nome']; ?>" title="Mudar Senha"><i class="fa-solid fa-key"></i></button>
                                <button class="btn-action btn-reset-pass" data-id="<?php echo $user['id']; ?>" data-nome="<?php echo $user['nome']; ?>" title="Resetar Senha"><i class="fa-solid fa-rotate-left"></i></button>
                                <button class="btn-action btn-change-email" data-id="<?php echo $user['id']; ?>" data-nome="<?php echo $user['nome']; ?>" data-email="<?php echo $user['email']; ?>" title="Trocar Email"><i class="fa-solid fa-envelope"></i></button>
                            </td>
                        </tr>
                        <?php
endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modais de Gerenciamento -->
    <div id="modal-container" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        
        <!-- Modal Mudar Senha -->
        <div id="modal-change-pass" class="content-card" style="max-width:400px; width:90%; display:none;">
            <h3 style="margin-bottom:1.5rem;">Mudar Senha de <span class="user-target-name"></span></h3>
            <form id="form-change-pass">
                <input type="hidden" name="id_usuario" class="user-target-id">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; color:#64748b;">Nova Senha</label>
                    <input type="password" name="nova_senha" class="form-control" placeholder="Mínimo 6 caracteres" required style="color:#334155; border:1px solid #e2e8f0; background:#f8fafc;">
                </div>
                <div style="display:flex; gap:1rem; margin-top:2rem;">
                    <button type="button" class="btn-secondary btn-close-modal" style="flex:1; padding:0.8rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">Cancelar</button>
                    <button type="submit" class="btn-primary" style="flex:1; padding:0.8rem;">Salvar</button>
                </div>
            </form>
        </div>

        <!-- Modal Trocar Email -->
        <div id="modal-change-email" class="content-card" style="max-width:400px; width:90%; display:none;">
            <h3 style="margin-bottom:1.5rem;">Trocar E-mail de <span class="user-target-name"></span></h3>
            <form id="form-change-email">
                <input type="hidden" name="id_usuario" class="user-target-id">
                <input type="hidden" name="action" value="change_email">
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; color:#64748b;">Novo E-mail</label>
                    <input type="email" name="novo_email" class="form-control user-target-email" required style="color:#334155; border:1px solid #e2e8f0; background:#f8fafc;">
                </div>
                <div style="display:flex; gap:1rem; margin-top:2rem;">
                    <button type="button" class="btn-secondary btn-close-modal" style="flex:1; padding:0.8rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">Cancelar</button>
                    <button type="submit" class="btn-primary" style="flex:1; padding:0.8rem;">Salvar</button>
                </div>
            </form>
        </div>

        <!-- Modal Novo Usuário -->
        <div id="modal-add-user" class="content-card" style="max-width:500px; width:90%; display:none;">
            <h3 style="margin-bottom:1.5rem;">Adicionar Novo Usuário</h3>
            <form id="form-add-user">
                <input type="hidden" name="action" value="add_user">
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; color:#64748b;">Nome Completo</label>
                    <input type="text" name="nome" class="form-control" placeholder="Ex: João Silva" required style="color:#334155; border:1px solid #e2e8f0; background:#f8fafc;">
                </div>
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; color:#64748b;">E-mail Corporativo</label>
                    <input type="email" name="email" class="form-control" placeholder="usuario@senai.br" required style="color:#334155; border:1px solid #e2e8f0; background:#f8fafc;">
                </div>
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; color:#64748b;">Nível de Acesso</label>
                    <select name="nivel" class="form-control" required style="color:#334155; border:1px solid #e2e8f0; background:#f8fafc; padding-left: 1rem;">
                        <option value="Auxiliar">Auxiliar</option>
                        <option value="Gerente">Gerente</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display:block; margin-bottom:0.5rem; font-size:0.9rem; color:#64748b;">Senha Provisória</label>
                    <input type="password" name="senha" class="form-control" placeholder="Mínimo 6 caracteres" required style="color:#334155; border:1px solid #e2e8f0; background:#f8fafc;">
                </div>
                <div style="display:flex; gap:1rem; margin-top:2rem;">
                    <button type="button" class="btn-secondary btn-close-modal" style="flex:1; padding:0.8rem; border-radius:8px; border:1px solid #e2e8f0; cursor:pointer;">Cancelar</button>
                    <button type="submit" class="btn-primary" style="flex:1; padding:0.8rem;">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-action:hover {
            background: #f1f5f9;
            color: #ff3b3b;
            border-color: #ff3b3b;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
    $(document).ready(function() {
        const table = $('#tabela-usuarios').DataTable({
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
                }
            },
            pageLength: 10,
            order: [[2, 'asc'], [0, 'asc']],
            columnDefs: [{ orderable: false, targets: [3] }]
        });

        // Funções de Modal
        function openModal(modalId, data) {
            $('#modal-container').css('display', 'flex');
            $('.content-card', '#modal-container').hide();
            $(modalId).show();
            
            if (data) {
                $('.user-target-id', modalId).val(data.id);
                $('.user-target-name', modalId).text(data.nome);
                if (data.email) $('.user-target-email', modalId).val(data.email);
            }
        }

        function closeModal() {
            $('#modal-container').hide();
            $('form', '#modal-container').trigger('reset');
        }

        $('.btn-close-modal').click(closeModal);

        $('.btn-add-user').click(function() {
            openModal('#modal-add-user');
        });

        // Ações
        $(document).on('click', '.btn-change-pass', function() {
            openModal('#modal-change-pass', {
                id: $(this).data('id'),
                nome: $(this).data('nome')
            });
        });

        $(document).on('click', '.btn-change-email', function() {
            openModal('#modal-change-email', {
                id: $(this).data('id'),
                nome: $(this).data('nome'),
                email: $(this).data('email')
            });
        });

        $(document).on('click', '.btn-reset-pass', function() {
            const id = $(this).data('id');
            const nome = $(this).data('nome');
            if (confirm(`Deseja realmente resetar a senha de ${nome} para Senai@SP?`)) {
                $.post('../../api/gerenciar_usuario.php', {
                    action: 'reset_password',
                    id_usuario: id
                }, function(response) {
                    const res = JSON.parse(response);
                    alert(res.message);
                });
            }
        });

        // Submit Forms
        $('#form-change-pass, #form-change-email, #form-add-user').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.post('../../api/gerenciar_usuario.php', formData, function(response) {
                const res = JSON.parse(response);
                alert(res.message);
                if (res.success) {
                    closeModal();
                    location.reload();
                }
            });
        });
    });
    </script>
</body>
</html>
