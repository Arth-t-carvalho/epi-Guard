DROP TABLE IF EXISTS logs_auditoria CASCADE;
DROP TABLE IF EXISTS evidencias CASCADE;
DROP TABLE IF EXISTS acoes_ocorrencia CASCADE;
DROP TABLE IF EXISTS ocorrencia_epis CASCADE;
DROP TABLE IF EXISTS ocorrencias CASCADE;
DROP TABLE IF EXISTS epis CASCADE;
DROP TABLE IF EXISTS funcionarios CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS setores CASCADE;
DROP TABLE IF EXISTS filiais CASCADE;

-- -----------------------------------------------------
-- 1. SETORES
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS setores (
  id SERIAL PRIMARY KEY,
  filial_id INT DEFAULT 1,
  nome VARCHAR(100) NOT NULL,
  nome_en VARCHAR(100),
  sigla VARCHAR(10),
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO',
  epis_json TEXT, -- Mantendo TEXT para compatibilidade simples, ou JSONB
  criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- 2. FUNCIONARIOS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS funcionarios (
  id SERIAL PRIMARY KEY,
  filial_id INT DEFAULT 1,
  nome VARCHAR(100) NOT NULL,
  setor_id INT,
  turno VARCHAR(20),
  foto_referencia VARCHAR(255),
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO',
  status_epi VARCHAR(20) NOT NULL DEFAULT 'CONFORME',
  ultima_atualizacao_status TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
  criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_funcionario_setor 
    FOREIGN KEY (setor_id) 
    REFERENCES setores(id) 
    ON DELETE SET NULL
);
CREATE INDEX idx_funcionario_setor ON funcionarios(setor_id);

-- -----------------------------------------------------
-- 3. TIPOS DE EPI
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS epis (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  nome_en VARCHAR(80),
  descricao TEXT,
  cor VARCHAR(10) DEFAULT '#E30613',
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO'
);

-- -----------------------------------------------------
-- 4. OCORRENCIAS DE SEGURANÇA
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS ocorrencias (
  id SERIAL PRIMARY KEY,
  funcionario_id INT NOT NULL,
  data_hora TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  filial_id INT DEFAULT 1,
  tipo VARCHAR(20) NOT NULL,
  oculto BOOLEAN NOT NULL DEFAULT FALSE,
  favorito BOOLEAN NOT NULL DEFAULT FALSE,
  criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ocorrencia_funcionario
    FOREIGN KEY (funcionario_id)
    REFERENCES funcionarios(id)
    ON DELETE CASCADE
);
CREATE INDEX idx_ocorrencia_funcionario ON ocorrencias(funcionario_id);
CREATE INDEX idx_ocorrencia_data ON ocorrencias(data_hora);

-- -----------------------------------------------------
-- 5. RELAÇÃO OCORRÊNCIA - EPIs
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS ocorrencia_epis (
  id SERIAL PRIMARY KEY,
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
);
CREATE INDEX idx_ocorrencia ON ocorrencia_epis(ocorrencia_id);
CREATE INDEX idx_epi ON ocorrencia_epis(epi_id);

-- -----------------------------------------------------
-- 6. USUARIOS DO SISTEMA
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id SERIAL PRIMARY KEY,
  filial_id INT DEFAULT 1,
  nome VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL,
  senha VARCHAR(255) NOT NULL,
  cargo VARCHAR(50) NOT NULL,
  setor_id INT,
  turno VARCHAR(20),
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO',
  pref_grafico VARCHAR(10) DEFAULT 'bar',
  criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uk_usuario UNIQUE (usuario),
  CONSTRAINT fk_usuario_setor
    FOREIGN KEY (setor_id)
    REFERENCES setores(id)
    ON DELETE SET NULL
);
CREATE INDEX idx_usuario_setor ON usuarios(setor_id);

-- -----------------------------------------------------
-- 7. AÇÕES DISCIPLINARES
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS acoes_ocorrencia (
  id SERIAL PRIMARY KEY,
  ocorrencia_id INT NOT NULL,
  usuario_id INT NOT NULL,
  tipo VARCHAR(50) NOT NULL,
  observacao TEXT,
  data_hora TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_acao_ocorrencia
    FOREIGN KEY (ocorrencia_id)
    REFERENCES ocorrencias(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_acao_usuario
    FOREIGN KEY (usuario_id)
    REFERENCES usuarios(id)
    ON DELETE RESTRICT
);
CREATE INDEX idx_acao_ocorrencia ON acoes_ocorrencia(ocorrencia_id);
CREATE INDEX idx_acao_usuario ON acoes_ocorrencia(usuario_id);

-- -----------------------------------------------------
-- 8. FILIAIS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS filiais (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cidade VARCHAR(100),
    estado CHAR(2),
    status VARCHAR(20) DEFAULT 'ATIVO',
    criado_em TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- 9. EVIDENCIAS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS evidencias (
  id SERIAL PRIMARY KEY,
  ocorrencia_id INT NOT NULL,
  caminho_imagem VARCHAR(255) NOT NULL,
  criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_evidencia_ocorrencia
    FOREIGN KEY (ocorrencia_id)
    REFERENCES ocorrencias(id)
    ON DELETE CASCADE
);
CREATE INDEX idx_evidencia_ocorrencia ON evidencias(ocorrencia_id);

-- -----------------------------------------------------
-- 10. LOGS DE AUDITORIA
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS logs_auditoria (
    id SERIAL PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(100) NOT NULL,
    tipo_entidade VARCHAR(50),
    entidade_id INT,
    valores_antigos TEXT,
    valores_novos TEXT,
    endereco_ip VARCHAR(45),
    criado_em TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

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
