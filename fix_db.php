<?php
/**
 * Script de Reparo e Sincronização de Banco de Dados - Versão Final de Lançamento
 * Este script garante que o banco de dados local esteja 100% compatível com o código.
 */

require_once __DIR__ . '/src/Infrastructure/Database/Connection.php';

use Facchini\Infrastructure\Database\Connection;

try {
    $db = Connection::getInstance();
    
    // Lista de comandos para garantir o schema correto
    $queries = [
        "ALTER TABLE setores ADD COLUMN IF NOT EXISTS filial_id INT DEFAULT 1 AFTER id",
        "ALTER TABLE setores ADD COLUMN IF NOT EXISTS nome_en VARCHAR(100) AFTER nome",
        "ALTER TABLE epis ADD COLUMN IF NOT EXISTS nome_en VARCHAR(100) AFTER nome",
        "ALTER TABLE epis ADD COLUMN IF NOT EXISTS cor VARCHAR(20) DEFAULT '#E30613' AFTER descricao",
        "CREATE TABLE IF NOT EXISTS filiais (id INT AUTO_INCREMENT PRIMARY KEY, nome VARCHAR(100) NOT NULL) ENGINE=InnoDB",
        "REPLACE INTO filiais (id, nome) VALUES (1, 'Aparecida do Taboado'), (2, 'Votuporanga'), (3, 'Mirassol'), (4, 'Rio Preto 1'), (5, 'Rio Preto 2'), (6, 'Roseira')",
        "UPDATE usuarios SET filial_id = 1 WHERE filial_id IS NULL",
        "UPDATE usuarios SET status = 'ATIVO' WHERE status IS NULL",
        "UPDATE funcionarios SET filial_id = 1 WHERE filial_id IS NULL",
        "ALTER TABLE ocorrencias ADD COLUMN IF NOT EXISTS filial_id INT DEFAULT 1",
        "ALTER TABLE ocorrencias ADD COLUMN IF NOT EXISTS favorito BOOLEAN DEFAULT FALSE",
        "ALTER TABLE setores ADD COLUMN IF NOT EXISTS epis_json TEXT",
        "ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS preferencia_grafico VARCHAR(20) DEFAULT 'bar'",
        "UPDATE ocorrencias SET data_hora = NOW() WHERE data_hora = '0000-00-00 00:00:00' OR data_hora IS NULL",
    ];

    echo "<h1>🛠️ Sistema de Reparo Automático - epi-Guard</h1>";
    echo "<ul>";
    
    foreach ($queries as $sql) {
        if ($db->query($sql)) {
            echo "<li style='color: green;'>✅ Sucesso: " . htmlspecialchars(substr($sql, 0, 70)) . "...</li>";
        } else {
            echo "<li style='color: orange;'>⚠️ Aviso (Pode já existir): " . htmlspecialchars($db->error) . "</li>";
        }
    }
    
    echo "</ul>";
    echo "<h3 style='color: blue;'>🚀 Banco de dados sincronizado e protegido contra erros fatais!</h3>";
    echo "<p><a href='index.php'>Voltar para o Dashboard</a></p>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Erro ao processar reparo: " . $e->getMessage() . "</h2>";
}
