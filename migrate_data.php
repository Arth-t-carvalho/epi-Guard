<?php
// migrate_data.php
require_once __DIR__ . '/src/Infrastructure/Database/Connection.php';
use Facchini\Infrastructure\Database\Connection;

try {
    $mysql = new PDO("mysql:host=localhost;dbname=epi_guard", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $pgsql = Connection::getInstance();
    
    echo "Limpando tabelas no PostgreSQL...\n";
    $pgsql->exec("TRUNCATE logs_auditoria, evidencias, acoes_ocorrencia, ocorrencia_epis, ocorrencias, epis, funcionarios, usuarios, setores, filiais RESTART IDENTITY CASCADE");

    $tables = [
        'filiais' => ['id', 'nome', 'cidade', 'estado', 'status', 'criado_em'],
        'setores' => ['id', 'filial_id', 'nome', 'sigla', 'status', 'epis_json', 'criado_em', 'atualizado_em'],
        'usuarios' => ['id', 'filial_id', 'nome', 'usuario', 'senha', 'cargo', 'setor_id', 'turno', 'status', 'pref_grafico', 'criado_em', 'atualizado_em'],
        'funcionarios' => ['id', 'filial_id', 'nome', 'setor_id', 'turno', 'foto_referencia', 'status', 'status_epi', 'ultima_atualizacao_status', 'criado_em', 'atualizado_em'],
        'epis' => ['id', 'nome', 'descricao', 'cor', 'status'],
        'ocorrencias' => ['id', 'funcionario_id', 'data_hora', 'filial_id', 'tipo', 'oculto', 'favorito', 'criado_em'],
        'ocorrencia_epis' => ['id', 'ocorrencia_id', 'epi_id'],
        'acoes_ocorrencia' => ['id', 'ocorrencia_id', 'usuario_id', 'tipo', 'observacao', 'data_hora'],
        'evidencias' => ['id', 'ocorrencia_id', 'caminho_imagem', 'criado_em'],
        'logs_auditoria' => ['id', 'usuario_id', 'acao', 'tipo_entidade', 'entidade_id', 'valores_antigos', 'valores_novos', 'endereco_ip', 'criado_em']
    ];

    foreach ($tables as $table => $columns) {
        echo "Migrando tabela: $table...\n";
        
        try {
            $stmt = $mysql->query("SELECT * FROM $table");
            $rows = $stmt->fetchAll();
        } catch (Exception $e) {
            echo "   (Tabela $table não existe no MySQL, pulando)\n";
            continue;
        }
        
        if (empty($rows)) {
            echo "   (Tabela vazia)\n";
            continue;
        }

        $colList = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $insert = $pgsql->prepare("INSERT INTO $table ($colList) VALUES ($placeholders)");

        $count = 0;
        foreach ($rows as $row) {
            $params = [];
            foreach ($columns as $col) {
                // Preprocessing to ensure compatibility
                $val = $row[$col] ?? null;
                
                // PostgreSQL boolean conversion
                if (in_array($col, ['oculto', 'favorito'])) {
                    $val = ($val == 1 || $val === true) ? 'true' : 'false';
                }
                
                $params[] = $val;
            }
            $insert->execute($params);
            $count++;
        }
        echo "   ($count registros migrados)\n";
        
        // Reset serial sequences
        $pgsql->exec("SELECT setval(pg_get_serial_sequence('$table', 'id'), (SELECT MAX(id) FROM $table))");
    }

    echo "\nMigração concluída com sucesso!\n";

} catch (Exception $e) {
    echo "\nERRO DURANTE MIGRAÇÃO: " . $e->getMessage() . "\n";
    echo "No código: " . $e->getCode() . "\n";
}
