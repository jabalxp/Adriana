-- Criação do Banco de Dados
CREATE DATABASE IF NOT EXISTS sistema_atestados DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistema_atestados;

-- Tabela de Usuários (Docentes e Gerentes)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('Gerente', 'Auxiliar', 'Administrador') NOT NULL DEFAULT 'Auxiliar',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Cursos
CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
);

-- Tabela de Turmas
CREATE TABLE IF NOT EXISTS turmas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    curso_id INT NOT NULL,
    periodo ENUM('Manhã', 'Tarde', 'Noite', 'Integral') NOT NULL,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

-- Tabela de Relacionamento Professor-Turma (N para N)
-- Um professor pode ter 3 a 4 cursos/turmas conforme Projeto.md
CREATE TABLE IF NOT EXISTS docente_turmas (
    usuario_id INT NOT NULL,
    turma_id INT NOT NULL,
    PRIMARY KEY (usuario_id, turma_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE
);

-- Tabela de Alunos
CREATE TABLE IF NOT EXISTS alunos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email_pessoal VARCHAR(100) NOT NULL UNIQUE,
    turma_id INT NOT NULL,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE
);

-- Tabela de Atestados
CREATE TABLE IF NOT EXISTS atestados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    turma_id INT NOT NULL, -- redundante para facilitar filtros por professor
    tipo ENUM('atestado_medico', 'tiro_guerra', 'judicial', 'obito', 'outro') NOT NULL,
    data_inicio DATE NULL,
    data_fim DATE NULL,
    cid_motivo VARCHAR(255) NULL,
    codigo_judicial VARCHAR(100) NULL,
    outro_motivo_desc VARCHAR(255) NULL,
    anexo_path VARCHAR(255) NOT NULL,
    status ENUM('Pendente', 'Aceito', 'Recusado') NOT NULL DEFAULT 'Pendente',
    professor_confirmou BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE
);

-- Inserção de Dados para Teste
INSERT INTO cursos (nome) VALUES ('Téc. Desenvolvimento de Sistemas'), ('Téc. Mecatrônica'), ('Mecânica Industrial');

INSERT INTO usuarios (nome, email, senha, nivel) VALUES 
('Gerente Principal', 'gerente@senai.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gerente'), -- senha: password
('Prof. Alexandre', 'alexandre@senai.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Auxiliar'),
('Admin Master', 'admin@senai.br', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador');

INSERT INTO turmas (nome, curso_id, periodo) VALUES 
('DS-2024-1', 1, 'Manhã'),
('MT-2024-1', 2, 'Tarde');

INSERT INTO docente_turmas (usuario_id, turma_id) VALUES (2, 1); -- Prof alexandre na turma DS
