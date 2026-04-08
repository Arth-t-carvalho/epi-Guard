-- -----------------------------------------------------
-- Esquema epi_guard para PostgreSQL
-- -----------------------------------------------------

-- Caso queira usar um schema específico:
-- CREATE SCHEMA IF NOT EXISTS epi_guard;
-- SET search_path TO epi_guard;

-- Função auxiliar para atualizar o campo atualizado_em automaticamente
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.atualizado_em = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- -----------------------------------------------------
-- 1. SETORES
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS setores (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  sigla VARCHAR(10),
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO' CHECK (status IN ('ATIVO', 'INATIVO')),
  epis_json JSONB, -- Slugs dos EPIs em formato JSONB para performance
  criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER update_setores_modtime 
BEFORE UPDATE ON setores 
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- -----------------------------------------------------
-- 2. FUNCIONARIOS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS funcionarios (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  setor_id INT,
  turno VARCHAR(20) CHECK (turno IN ('MANHA', 'TARDE', 'NOITE', 'INTEGRAL')),
  foto_referencia VARCHAR(255),
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO' CHECK (status IN ('ATIVO', 'INATIVO', 'AFASTADO')),
  status_epi VARCHAR(20) NOT NULL DEFAULT 'CONFORME' CHECK (status_epi IN ('CONFORME', 'NAO_CONFORME')),
  ultima_atualizacao_status TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
  criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_funcionario_setor 
    FOREIGN KEY (setor_id) 
    REFERENCES setores(id) 
    ON DELETE SET NULL
);

CREATE TRIGGER update_funcionarios_modtime 
BEFORE UPDATE ON funcionarios 
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- -----------------------------------------------------
-- 3. TIPOS DE EPI
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS epis (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(80) NOT NULL,
  descricao TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO' CHECK (status IN ('ATIVO', 'INATIVO'))
);

-- -----------------------------------------------------
-- 4. OCORRENCIAS DE SEGURANÇA
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS ocorrencias (
  id SERIAL PRIMARY KEY,
  funcionario_id INT NOT NULL,
  data_hora TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('INFRACAO', 'CONFORMIDADE')),
  favorito BOOLEAN NOT NULL DEFAULT FALSE,
  oculto BOOLEAN NOT NULL DEFAULT FALSE,
  criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ocorrencia_funcionario
    FOREIGN KEY (funcionario_id)
    REFERENCES funcionarios(id)
    ON DELETE CASCADE
);

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

-- -----------------------------------------------------
-- 6. USUARIOS DO SISTEMA
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id SERIAL PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL,
  senha VARCHAR(255) NOT NULL, -- Hash Bcrypt ou Argon2
  cargo VARCHAR(50) NOT NULL CHECK (cargo IN ('SUPER_ADMIN', 'SUPERVISOR', 'GERENTE_SEGURANCA')),
  setor_id INT,
  turno VARCHAR(20) CHECK (turno IN ('MANHA', 'TARDE', 'NOITE', 'INTEGRAL')),
  status VARCHAR(20) NOT NULL DEFAULT 'ATIVO' CHECK (status IN ('ATIVO', 'INATIVO')),
  criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uk_usuario UNIQUE (usuario),
  CONSTRAINT fk_usuario_setor
    FOREIGN KEY (setor_id)
    REFERENCES setores(id)
    ON DELETE SET NULL
);

CREATE TRIGGER update_usuarios_modtime 
BEFORE UPDATE ON usuarios 
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- -----------------------------------------------------
-- 7. AÇÕES DISCIPLINARES
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS acoes_ocorrencia (
  id SERIAL PRIMARY KEY,
  ocorrencia_id INT NOT NULL,
  usuario_id INT NOT NULL,
  tipo VARCHAR(50) NOT NULL CHECK (tipo IN ('OBSERVACAO', 'ADVERTENCIA_VERBAL', 'ADVERTENCIA_ESCRITA', 'SUSPENSAO')),
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

-- -----------------------------------------------------
-- 8. AMOSTRAS FACIAIS
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS amostras_faciais (
  id SERIAL PRIMARY KEY,
  funcionario_id INT NOT NULL,
  caminho_imagem VARCHAR(255) NOT NULL,
  criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_amostra_funcionario
    FOREIGN KEY (funcionario_id)
    REFERENCES funcionarios(id)
    ON DELETE CASCADE
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
