<?php
require_once '../../api/require.php';

$email_aluno = $_GET['email'] ?? '';

// Buscar cursos do banco
$result = mysqli_query($conn, "SELECT * FROM cursos ORDER BY nome ASC");
$cursos = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Atestado - Aluno SENAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body style="background: #f1f5f9; color: #334155;">
    <div class="premium-container" style="display: block; padding: 2rem;">
        <div class="content-card" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header" style="border-bottom: 2px solid #f1f5f9; padding-bottom: 1.5rem;">
                <div class="card-title">
                    <i class="fa-solid fa-file-circle-plus" style="color: #ff3b3b;"></i>
                    Cadastro de Atestado / Justificativa
                </div>
                <div style="font-size: 0.85rem; color: #64748b;">
                    <i class="fa-solid fa-user"></i> E-mail Pessoal: <strong><?php echo htmlspecialchars($email_aluno); ?></strong>
                </div>
            </div>

            <form action="../../api/process_atestado.php" method="POST" enctype="multipart/form-data" style="margin-top: 2rem;">
                <input type="hidden" name="email_pessoal" value="<?php echo htmlspecialchars($email_aluno); ?>">
                
                <!-- Curso / Turma -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Curso / Turma:</label>
                    <select name="turma_id" class="form-control" style="background: #fff; color: #334155; border: 1px solid #cbd5e1;" required>
                        <option value="">Selecione...</option>
                        <?php
// Em um cenário real, usaríamos AJAX para filtrar turmas por curso. 
// Aqui vamos listar as turmas do banco.
$result_turmas = mysqli_query($conn, "SELECT t.id, t.nome, c.nome as curso_nome FROM turmas t JOIN cursos c ON t.curso_id = c.id");
while ($t = mysqli_fetch_assoc($result_turmas)) {
    echo "<option value='{$t['id']}'>{$t['curso_nome']} - {$t['nome']}</option>";
}
?>
                    </select>
                </div>

                <!-- Nome do Aluno -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Nome do Aluno:</label>
                    <select name="aluno_id" id="aluno_id" class="form-control" style="background: #fff; color: #334155; border: 1px solid #cbd5e1;" required disabled>
                        <option value="">Selecione a turma primeiro...</option>
                    </select>
                    <input type="text" name="nome_aluno_novo" id="novo_aluno_nome" class="form-control" style="display:none; margin-top: 0.5rem; padding-left: 1rem; background: #fff; border: 1px solid #cbd5e1; color: #334155;" placeholder="Digite seu nome completo">
                </div>

                <!-- Tipo de Documento -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Documento que deseja enviar:</label>
                    <select name="tipo_documento" id="tipo_documento" class="form-control" style="background: #fff; color: #334155; border: 1px solid #cbd5e1;" required>
                        <option value="">Selecione o tipo...</option>
                        <option value="atestado_medico">Atestado/Declaração Médica</option>
                        <option value="tiro_guerra">Declaração Tiro de Guerra</option>
                        <option value="judicial">Declaração ou Atestado Judicial</option>
                        <option value="obito">Atestado de Óbito de Familiar de 1º Grau</option>
                        <option value="outro">Outra</option>
                    </select>
                </div>

                <!-- Campos Dinâmicos -->
                <div id="dynamic-fields" style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; display: none; border-left: 4px solid #ff3b3b;">
                    
                    <!-- Outro Motivo (Input Texto) -->
                    <div id="field-outro-motivo" style="display:none; margin-bottom: 1rem;">
                        <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Especifique o motivo:</label>
                        <input type="text" name="outro_motivo" class="form-control" style="padding-left: 1rem; background: #fff; border: 1px solid #cbd5e1; color: #334155;">
                    </div>

                    <!-- Datas (Médico) -->
                    <div id="field-datas" style="display:none; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Data de Início:</label>
                            <input type="date" name="data_inicio" class="form-control" style="padding-left: 1rem; background: #fff; border: 1px solid #cbd5e1; color: #334155;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Data de Término:</label>
                            <input type="date" name="data_fim" class="form-control" style="padding-left: 1rem; background: #fff; border: 1px solid #cbd5e1; color: #334155;">
                        </div>
                    </div>

                    <!-- CID / Código / Motivo -->
                    <div id="field-cid" style="display:none;">
                        <label id="label-cid" style="display:block; margin-bottom:0.5rem; font-weight:600;">CID ou Motivo:</label>
                        <input type="text" name="cid_motivo" class="form-control" style="padding-left: 1rem; background: #fff; border: 1px solid #cbd5e1; color: #334155;">
                    </div>

                    <!-- Código Judicial -->
                    <div id="field-judicial" style="display:none;">
                        <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Código da Declaração Judicial:</label>
                        <input type="text" name="codigo_judicial" class="form-control" style="padding-left: 1rem; background: #fff; border: 1px solid #cbd5e1; color: #334155;" placeholder="Ex: 123.456.789">
                    </div>
                </div>

                <!-- Upload de Arquivo (Sempre visível conforme Projeto.md) -->
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Anexar Foto do Atestado / Documento:</label>
                    <div style="border: 2px dashed #cbd5e1; padding: 2rem; border-radius: 8px; text-align: center; background: #fff; color: #334155;">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2rem; color: #94a3b8; margin-bottom: 1rem;"></i>
                        <input type="file" name="anexo" id="anexo" accept="image/*,.pdf" required style="display: block; margin: 0 auto;">
                        <p style="font-size: 0.8rem; color: #64748b; margin-top: 1rem;">Formatos aceitos: JPG, PNG, PDF (Máx. 5MB)</p>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <a href="index.php" class="btn-primary" style="background: #64748b; width: auto; padding: 1rem 2rem;">
                        <i class="fa-solid fa-chevron-left"></i> Voltar
                    </a>
                    <button type="submit" class="btn-primary" style="flex-grow:1;">
                        Enviar Documento <i class="fa-solid fa-paper-plane" style="margin-left: 0.5rem;"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const turmaSelect = document.querySelector('select[name="turma_id"]');
        const alunoSelect = document.getElementById('aluno_id');
        const novoAlunoInput = document.getElementById('novo_aluno_nome');
        const tipoDoc = document.getElementById('tipo_documento');
        const dynamicContainer = document.getElementById('dynamic-fields');
        
        const fieldOutroMotivo = document.getElementById('field-outro-motivo');
        const fieldDatas = document.getElementById('field-datas');
        const fieldCID = document.getElementById('field-cid');
        const fieldJudicial = document.getElementById('field-judicial');
        const labelCID = document.getElementById('label-cid');

        // Busca de alunos por turma via AJAX
        turmaSelect.addEventListener('change', async function() {
            alunoSelect.innerHTML = '<option value="">Carregando...</option>';
            alunoSelect.disabled = true;
            novoAlunoInput.style.display = 'none';
            novoAlunoInput.required = false;
            
            if(this.value) {
                try {
                    const res = await fetch(`../../api/get_alunos_by_turma.php?turma_id=${this.value}`);
                    const alunos = await res.json();
                    
                    alunoSelect.innerHTML = '<option value="">Selecione seu nome...</option>';
                    alunos.forEach(a => {
                        alunoSelect.innerHTML += `<option value="${a.id}">${a.nome}</option>`;
                    });
                    
                    alunoSelect.innerHTML += '<option value="novo">+ Não encontrei meu nome (Cadastrar-se)</option>';
                    alunoSelect.disabled = false;
                } catch(e) {
                    alunoSelect.innerHTML = '<option value="">Erro ao carregar alunos. Tente novamente.</option>';
                }
            } else {
                alunoSelect.innerHTML = '<option value="">Selecione a turma primeiro...</option>';
            }
        });

        alunoSelect.addEventListener('change', function() {
            if(this.value === 'novo') {
                novoAlunoInput.style.display = 'block';
                novoAlunoInput.required = true;
            } else {
                novoAlunoInput.style.display = 'none';
                novoAlunoInput.required = false;
            }
        });

        tipoDoc.addEventListener('change', function() {
            const val = this.value;
            
            // Elementos de data
            const inputDataInicio = document.querySelector('input[name="data_inicio"]');
            const inputDataFim = document.querySelector('input[name="data_fim"]');
            
            // Reset fields
            dynamicContainer.style.display = val ? 'block' : 'none';
            fieldOutroMotivo.style.display = 'none';
            fieldDatas.style.display = 'none';
            fieldCID.style.display = 'none';
            fieldJudicial.style.display = 'none';
            
            // Reset required das datas para evitar erro de invalid form control not focusable
            inputDataInicio.required = false;
            inputDataFim.required = false;

            if (val === 'atestado_medico') {
                fieldDatas.style.display = 'grid';
                inputDataInicio.required = true;
                inputDataFim.required = true;
                fieldCID.style.display = 'block';
                labelCID.innerText = 'CID ou MOTIVO do afastamento:';
            } else if (val === 'tiro_guerra') {
                // Só aparece a opção do anexo (já é fixa)
                dynamicContainer.style.display = 'none';
            } else if (val === 'judicial') {
                fieldJudicial.style.display = 'block';
            } else if (val === 'obito') {
                fieldCID.style.display = 'block';
                labelCID.innerText = 'CID / Causa:';
            } else if (val === 'outro') {
                fieldOutroMotivo.style.display = 'block';
                fieldCID.style.display = 'block';
                labelCID.innerText = 'Código do atestado (ex: CID):';
            }
        });
    </script>
</body>
</html>
