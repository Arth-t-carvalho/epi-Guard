<?php
/**
 * Script temporário para criar a tabela 'maquinas' no PostgreSQL
 */

require_once __DIR__ . '/../src/Infrastructure/Database/Connection.php';
use Facchini\Infrastructure\Database\Connection;

try {
    $pdo = Connection::getInstance();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS maquinas (
      id SERIAL PRIMARY KEY,
      nome VARCHAR(100) NOT NULL,
      setor_id INT NOT NULL,
      epi_id INT,
      criado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
      atualizado_em TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_maquina_setor 
        FOREIGN KEY (setor_id) 
        REFERENCES setores(id) 
        ON DELETE CASCADE,
      CONSTRAINT fk_maquina_epi 
        FOREIGN KEY (epi_id) 
        REFERENCES epis(id) 
        ON DELETE SET NULL
    );
    CREATE INDEX IF NOT EXISTS idx_maquina_setor ON maquinas(setor_id);
    ";

    $pdo->exec($sql);
    echo "Tabela 'maquinas' criada com sucesso!\n";

} catch (Exception $e) {
    echo "ERRO ao criar tabela: " . $e->getMessage() . "\n";
}
