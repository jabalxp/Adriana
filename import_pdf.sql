-- Script de Importação dos Dados do PDF (Destaque para as 2 primeiras tabelas)
USE sistema_atestados;

-- 1. Inserção de Cursos (Garantindo que existam)
-- Usamos INSERT IGNORE para não duplicar se já houver
INSERT IGNORE INTO cursos (nome) VALUES 
('DEV_SESI_1A'), 
('DEV_SESI_1B'), 
('M_EAD_ECT_850'), 
('MALP1A'), 
('MALP1B');

-- 2. Inserção de Turmas relacionadas aos Cursos
-- Assumindo que o nome da turma é igual ao do curso para este mapeamento inicial
INSERT IGNORE INTO turmas (nome, curso_id, periodo) 
SELECT 'DEV_SESI_1A', id, 'Manhã' FROM cursos WHERE nome = 'DEV_SESI_1A' UNION
SELECT 'DEV_SESI_1B', id, 'Manhã' FROM cursos WHERE nome = 'DEV_SESI_1B' UNION
SELECT 'M_EAD_ECT_850', id, 'Integral' FROM cursos WHERE nome = 'M_EAD_ECT_850' UNION
SELECT 'MALP1A', id, 'Tarde' FROM cursos WHERE nome = 'MALP1A' UNION
SELECT 'MALP1B', id, 'Tarde' FROM cursos WHERE nome = 'MALP1B';

-- 3. Inserção de Alunos (Extraídos das Tabelas 1 e 2 do PDF)
-- Turma: DEV_SESI_1A
SET @turma_id = (SELECT id FROM turmas WHERE nome = 'DEV_SESI_1A' LIMIT 1);
INSERT IGNORE INTO alunos (nome, email_pessoal, turma_id) VALUES
('ANA BEATRIZ EVANGELISTA', 'ana.evangelista@aluno.senai.br', @turma_id),
('ANA BEATRIZ FERNANDES', 'ana.fernandes5@aluno.senai.br', @turma_id),
('ANDRÉ JUNIOR RIBEIRO', 'andre.junior@aluno.senai.br', @turma_id),
('CAIO HENRIQUE FERNANDES', 'caio.fernandes3@aluno.senai.br', @turma_id),
('CAIO HENRIQUE OLIVEIRA', 'caio.oliveira6@aluno.senai.br', @turma_id),
('CARLOS EDUARDO FERNANDES', 'carlos.fernandes11@aluno.senai.br', @turma_id),
('CLARA BEATRIZ RIBEIRO', 'clara.ribeiro5@aluno.senai.br', @turma_id),
('DAVI HENRIQUE FERREIRA', 'davi.ferreira9@aluno.senai.br', @turma_id),
('DIOGO HENRIQUE RIBEIRO', 'diogo.ribeiro@aluno.senai.br', @turma_id),
('EDUARDO MENDES EVANGELISTA', 'eduardo.evangelista@aluno.senai.br', @turma_id);

-- Turma: DEV_SESI_1B
SET @turma_id = (SELECT id FROM turmas WHERE nome = 'DEV_SESI_1B' LIMIT 1);
INSERT IGNORE INTO alunos (nome, email_pessoal, turma_id) VALUES
('ANA PAULA DE SOUZA LIMA', 'ana.paula28@aluno.senai.br', @turma_id),
('ANDREY GABRIEL LOPES GOMES', 'andrey.gomes@aluno.senai.br', @turma_id),
('ARTHUR DA SILVA PEREIRA', 'arthur.pereira26@aluno.senai.br', @turma_id),
('EMILLY VITORIA DE OLIVEIRA', 'emilly.vitoria3@aluno.senai.br', @turma_id),
('FELIPE HENRIQUE SANTOS', 'felipe.santos79@aluno.senai.br', @turma_id),
('GABRIEL DE OLIVEIRA RODRIGUES', 'gabriel.rodrigues75@aluno.senai.br', @turma_id),
('GIOVANA LUIZA DE SOUZA', 'giovana.luiza@aluno.senai.br', @turma_id),
('GUSTAVO HENRIQUE DA SILVA', 'gustavo.silva117@aluno.senai.br', @turma_id),
('HELENA DE SOUSA SILVA', 'helena.silva16@aluno.senai.br', @turma_id),
('HENRIQUE DE OLIVEIRA SANTOS', 'henrique.santos93@aluno.senai.br', @turma_id),
('ISABELLA VITORIA LOPES', 'isabella.lopes12@aluno.senai.br', @turma_id),
('JOÃO PEDRO DE ALMEIDA', 'joao.almeida58@aluno.senai.br', @turma_id),
('JÚLIA DE OLIVEIRA SANTOS', 'julia.santos170@aluno.senai.br', @turma_id),
('LUCAS DE OLIVEIRA SANTOS', 'lucas.santos177@aluno.senai.br', @turma_id),
('VICTOR HUGO SILVA DE SOUZA', 'victor.souza71@aluno.senai.br', @turma_id),
('VITOR LANDIN', 'vitor.landin@aluno.senai.br', @turma_id),
('YAN REMEDI SILVA', 'yan.silva8@aluno.senai.br', @turma_id);

-- Turma: M_EAD_ECT_850
SET @turma_id = (SELECT id FROM turmas WHERE nome = 'M_EAD_ECT_850' LIMIT 1);
INSERT IGNORE INTO alunos (nome, email_pessoal, turma_id) VALUES
('ANA JULIA DOS SANTOS', 'ana.julia104@aluno.senai.br', @turma_id),
('ANDREY LUIZ DE OLIVEIRA', 'andrey.oliveira@aluno.senai.br', @turma_id),
('ARTHUR DE ALMEIDA SANTOS', 'arthur.almeida25@aluno.senai.br', @turma_id),
('CAIO HENRIQUE DE SOUZA', 'caio.souza93@aluno.senai.br', @turma_id),
('EDUARDO DE OLIVEIRA SILVA', 'eduardo.silva188@aluno.senai.br', @turma_id),
('FELIPE DE SOUSA SANTOS', 'felipe.santos80@aluno.senai.br', @turma_id),
('GABRIEL DE OLIVEIRA SILVA', 'gabriel.silva202@aluno.senai.br', @turma_id),
('GIOVANA DE SOUSA SILVA', 'giovana.sousa11@aluno.senai.br', @turma_id),
('GUSTAVO DE OLIVEIRA SANTOS', 'gustavo.santos203@aluno.senai.br', @turma_id),
('HELENA DE OLIVEIRA SILVA', 'helena.silva17@aluno.senai.br', @turma_id),
('HANNAH LUISA ESTEVAM LEITE', 'hannah.leite@aluno.senai.br', @turma_id),
('LINEA GIOVANNA DE ALMEIDA', 'linea.almeida@aluno.senai.br', @turma_id),
('PALOMA QUEMILLY PAIXÃO HERNANDES', 'paloma.hernandes@aluno.senai.br', @turma_id),
('YUDY ANTÔNO PEREIRA DE SOUZA', 'yudy.souza@aluno.senai.br', @turma_id);

-- Turma: MALP1A
SET @turma_id = (SELECT id FROM turmas WHERE nome = 'MALP1A' LIMIT 1);
INSERT IGNORE INTO alunos (nome, email_pessoal, turma_id) VALUES
('ALAN ANDRADE DOS SANTOS', 'alan.andrade@aluno.senai.br', @turma_id),
('CAROLINE VICTORIA LIMA BITENCOURT', 'caroline.bitencourt@aluno.senai.br', @turma_id),
('DIEGO SILVA VIEIRA DA ROCHA', 'diego.rocha@aluno.senai.br', @turma_id),
('FLAVIA RODRIGUES FERREIRA', 'flavia.ferreira12@aluno.senai.br', @turma_id),
('ITHANY SANTOS SILVA', 'ithany.silva@aluno.senai.br', @turma_id),
('LUANA CALDEIRA RODRIGUES', 'luana.rodrigues32@aluno.senai.br', @turma_id),
('MARIA CLARA FELICIANO', 'maria.feliciano1@aluno.senai.br', @turma_id),
('MARIA EDUARDA DOS SANTOS SILVA', 'maria.silva970@aluno.senai.br', @turma_id),
('MATHEUS FELIPE DA SILVA VILAR', 'matheus.vilar@aluno.senai.br', @turma_id),
('MATHEUS ROSSANO MOVIO', 'matheus.movio@aluno.senai.br', @turma_id),
('NATALLY VITORIA RIBEIRO ALVES', 'natally.alves@aluno.senai.br', @turma_id),
('NICOLLY AGUIAR BRAGA', 'nicolly.braga@aluno.senai.br', @turma_id),
('POLIANA SILVA NASCIMENTO', 'poliana.nascimento7@aluno.senai.br', @turma_id),
('RAFAELA DE AZEVEDO CHIQUETTO DA SILVA', 'rafaela.silva111@aluno.senai.br', @turma_id),
('RAQUEL MAXIMO ALEXANDRE', 'raquel.alexandre@aluno.senai.br', @turma_id),
('VITHÓRIA FERREIRA DA SILVA', 'vithoria.silva10@aluno.senai.br', @turma_id),
('VITORIA BEATRIZ FARIA DE CARVALHO', 'vitoria.carvalho21@aluno.senai.br', @turma_id),
('YONNARA OLIVEIRA TEIXEIRA', 'yonnara.teixeira@aluno.senai.br', @turma_id),
('YURI BARUSSI DE SOUZA', 'yuri.souza8@aluno.senai.br', @turma_id);

-- Turma: MALP1B
SET @turma_id = (SELECT id FROM turmas WHERE nome = 'MALP1B' LIMIT 1);
INSERT IGNORE INTO alunos (nome, email_pessoal, turma_id) VALUES
('BRUNA EDUARDA SANTOS SOUZA', 'bruna.souza124@aluno.senai.br', @turma_id),
('FABRICIO DA ROCHA MARQUETTI', 'fabricio.marquetti@aluno.senai.br', @turma_id),
('GIOVANI CORREIA DA SILVA OTTOBONI', 'giovani.ottoboni@aluno.senai.br', @turma_id),
('GUSTAVO HENRIQUE OLIVEIRA DA SILVA', 'gustavo.silva118@aluno.senai.br', @turma_id),
('HILLARY DANTAS DE OLIVEIRA PEREIRA', 'hillary.pereira@aluno.senai.br', @turma_id),
('LEONARDO FERRARI CAPELLI', 'leonardo.capelli@aluno.senai.br', @turma_id),
('LUIZ HENRIQUE DOS SANTOS CORREA', 'luiz.correa1@aluno.senai.br', @turma_id),
('MILENA RODRIGUES VALENTIM', 'milena.valentim@aluno.senai.br', @turma_id);
