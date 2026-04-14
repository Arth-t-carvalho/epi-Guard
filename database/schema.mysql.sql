-- -----------------------------------------------------
-- Esquema epi_guard para MySQL (Refinado)
-- -----------------------------------------------------

-- -----------------------------------------------------
-- 0. FILIAIS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS filiais (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  cidade VARCHAR(100),
  estado CHAR(2),
  status VARCHAR(20) DEFAULT 'ATIVO',
  cor_grafico_total VARCHAR(10) DEFAULT '#10B981',
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 1. SETORES
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS setores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filial_id INT DEFAULT 1,
  nome VARCHAR(100) NOT NULL,
  nome_en VARCHAR(100),
  sigla VARCHAR(10),
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO' CHECK (status IN ('ATIVO', 'INATIVO')),
  epis_json JSON,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deletado_em DATETIME, -- Soft Delete
  CONSTRAINT fk_setor_filial
    FOREIGN KEY (filial_id)
    REFERENCES filiais(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 2. FUNCIONARIOS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS funcionarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filial_id INT DEFAULT 1,
  nome VARCHAR(100) NOT NULL,
  matricula VARCHAR(20) UNIQUE,
  setor_id INT,
  turno VARCHAR(20) CHECK (turno IN ('MANHA', 'TARDE', 'NOITE', 'INTEGRAL')),
  foto_referencia VARCHAR(255),
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO' CHECK (status IN ('ATIVO', 'INATIVO', 'AFASTADO')),
  status_epi VARCHAR(20) NOT NULL DEFAULT 'CONFORME' CHECK (status_epi IN ('CONFORME', 'NAO_CONFORME')),
  ultima_atualizacao_status DATETIME DEFAULT CURRENT_TIMESTAMP,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deletado_em DATETIME, -- Soft Delete
  CONSTRAINT fk_funcionario_setor 
    FOREIGN KEY (setor_id) 
    REFERENCES setores(id) 
    ON DELETE SET NULL,
  CONSTRAINT fk_funcionario_filial
    FOREIGN KEY (filial_id)
    REFERENCES filiais(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_funcionario_setor ON funcionarios(setor_id);
CREATE INDEX idx_funcionario_deletado ON funcionarios(deletado_em);

-- -----------------------------------------------------
-- 3. TIPOS DE EPI
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS epis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  nome_en VARCHAR(80),
  descricao TEXT,
  cor VARCHAR(10) DEFAULT '#E30613',
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO' CHECK (status IN ('ATIVO', 'INATIVO')),
  deletado_em DATETIME -- Soft Delete
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 4. MAQUINAS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS maquinas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  setor_id INT NOT NULL,
  epi_id INT,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deletado_em DATETIME, -- Soft Delete
  CONSTRAINT fk_maquina_setor
    FOREIGN KEY (setor_id)
    REFERENCES setores(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_maquina_epi
    FOREIGN KEY (epi_id)
    REFERENCES epis(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_maquina_setor ON maquinas(setor_id);
CREATE INDEX idx_maquina_deletado ON maquinas(deletado_em);

-- -----------------------------------------------------
-- 5. OCORRENCIAS DE SEGURANÇA
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS ocorrencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  funcionario_id INT NOT NULL,
  filial_id INT DEFAULT 1,
  data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('INFRACAO', 'CONFORMIDADE')),
  favorito BOOLEAN NOT NULL DEFAULT FALSE,
  oculto BOOLEAN NOT NULL DEFAULT FALSE,
  foto_evidencia VARCHAR(255), -- Caminho da imagem principal (Otimização)
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ocorrencia_funcionario
    FOREIGN KEY (funcionario_id)
    REFERENCES funcionarios(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_ocorrencia_filial
    FOREIGN KEY (filial_id)
    REFERENCES filiais(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_ocorrencia_funcionario ON ocorrencias(funcionario_id);
CREATE INDEX idx_ocorrencia_data ON ocorrencias(data_hora);

-- -----------------------------------------------------
-- 6. RELAÇÃO OCORRÊNCIA - EPIs
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS ocorrencia_epis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ocorrencia_id INT NOT NULL,
  epi_id INT NOT NULL,
  CONSTRAINT fk_ocorrencia_epi_ocorrencia
    FOREIGN KEY (ocorrencia_id)
    REFERENCES ocorrencias(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_ocorrencia_epi_epi
    FOREIGN KEY (epi_id)
    REFERENCES epis(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 7. USUARIOS DO SISTEMA
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filial_id INT DEFAULT 1,
  nome VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  cargo VARCHAR(50) NOT NULL CHECK (cargo IN ('SUPER_ADMIN', 'SUPERVISOR', 'GERENTE_SEGURANCA')),
  setor_id INT,
  turno VARCHAR(20) CHECK (turno IN ('MANHA', 'TARDE', 'NOITE', 'INTEGRAL')),
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO' CHECK (status IN ('ATIVO', 'INATIVO')),
  pref_grafico VARCHAR(10) DEFAULT 'bar',
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deletado_em DATETIME, -- Soft Delete
  CONSTRAINT fk_usuario_setor
    FOREIGN KEY (setor_id)
    REFERENCES setores(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_usuario_filial
    FOREIGN KEY (filial_id)
    REFERENCES filiais(id)
    ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_usuario_deletado ON usuarios(deletado_em);

-- -----------------------------------------------------
-- 8. AÇÕES DISCIPLINARES, AMOSTRAS E EVIDENCIAS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS acoes_ocorrencia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ocorrencia_id INT NOT NULL,
  usuario_id INT NOT NULL,
  tipo VARCHAR(50) NOT NULL CHECK (tipo IN ('OBSERVACAO', 'ADVERTENCIA_VERBAL', 'ADVERTENCIA_ESCRITA', 'SUSPENSAO')),
  observacao TEXT,
  data_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_acao_ocorrencia FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id) ON DELETE CASCADE,
  CONSTRAINT fk_acao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS amostras_faciais (
  id INT AUTO_INCREMENT PRIMARY KEY,
  funcionario_id INT NOT NULL,
  caminho_imagem VARCHAR(255) NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_amostra_funcionario FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS evidencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ocorrencia_id INT NOT NULL,
  caminho_imagem VARCHAR(255) NOT NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_evidencia_ocorrencia FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- 9. AUDIT LOGS (Sincronizado com Repositório PHP)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT,
  acao VARCHAR(50) NOT NULL,
  tipo_entidade VARCHAR(50) NOT NULL,
  entidade_id INT,
  valores_antigos JSON,
  valores_novos JSON,
  endereco_ip VARCHAR(45),
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_audit_usuario ON audit_logs(usuario_id);
CREATE INDEX idx_audit_criado ON audit_logs(criado_em);

-- -----------------------------------------------------
-- INSERÇÃO DE DADOS INICIAIS
-- -----------------------------------------------------
INSERT INTO filiais (nome, cidade, estado, status) VALUES 
('Aparecida do Taboado', 'Aparecida do Taboado', 'MS', 'ATIVO'),
('Votuporanga', 'Votuporanga', 'SP', 'ATIVO');

INSERT INTO epis (nome, descricao, cor) VALUES 
('Capacete', 'Proteção para a cabeça', '#E30613'),
('Óculos', 'Proteção ocular', '#E30613'),
('Protetor Auricular', 'Proteção auditiva', '#E30613');
