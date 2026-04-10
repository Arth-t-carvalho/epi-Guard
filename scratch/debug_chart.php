<?php
// Simular sessão para debug
session_start();
$_SESSION['active_filial_id'] = 1;

require __DIR__ . '/../src/Infrastructure/Database/Connection.php';
$db = Facchini\Infrastructure\Database\Connection::getInstance();

echo "=== 1. EPIs ativos ===\n";
$s = $db->query("SELECT id, nome, status, cor FROM epis WHERE status = 'ATIVO' ORDER BY id");
$epis = $s->fetchAll(PDO::FETCH_ASSOC);
print_r($epis);

echo "\n=== 2. Setores da filial 1 ===\n";
$s2 = $db->query("SELECT id, nome, epis_json FROM setores WHERE filial_id = 1 AND status = 'ATIVO'");
$setores = $s2->fetchAll(PDO::FETCH_ASSOC);
print_r($setores);

echo "\n=== 3. Ocorrencias por EPI (tabela ocorrencia_epis) ===\n";
$s3 = $db->query("
    SELECT e.id, e.nome, COUNT(*) as total 
    FROM ocorrencia_epis oe 
    JOIN epis e ON oe.epi_id = e.id 
    JOIN ocorrencias o ON oe.ocorrencia_id = o.id
    JOIN funcionarios f ON o.funcionario_id = f.id
    WHERE f.filial_id = 1 AND EXTRACT(YEAR FROM o.data_hora) = 2026
    GROUP BY e.id, e.nome 
    ORDER BY e.nome
");
print_r($s3->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== 4. Cor da filial 1 ===\n";
$s4 = $db->query("SELECT id, cor_grafico_total FROM filiais WHERE id = 1");
print_r($s4->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== 5. Simular resolveEpiSlugsToNames ===\n";
$sectorIds = [];
foreach ($setores as $s) {
    $sectorIds[] = $s['id'];
}
echo "Sector IDs: " . implode(', ', $sectorIds) . "\n";

if (!empty($sectorIds)) {
    $slugs = [];
    foreach ($setores as $row) {
        if (!empty($row['epis_json'])) {
            $json = json_decode($row['epis_json'], true) ?: [];
            foreach ($json as $epi) {
                if (is_string($epi)) $slugs[] = $epi;
                elseif (isset($epi['nome'])) $slugs[] = $epi['nome'];
            }
        }
    }
    $slugs = array_unique($slugs);
    echo "Slugs do epis_json: ";
    print_r($slugs);
    
    $allEpiNames = array_column($epis, 'nome');
    echo "Nomes EPIs ativos: ";
    print_r($allEpiNames);
    
    $resolved = [];
    foreach ($slugs as $slug) {
        $normalizedSlug = strtolower($slug);
        $fuzzySlug = str_replace(['ó', 'ò', 'ô', 'õ', 'á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ú'], ['o', 'o', 'o', 'o', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'u'], $normalizedSlug);
        
        foreach ($allEpiNames as $fullName) {
            $normalizedName = strtolower($fullName);
            $fuzzyName = str_replace(['ó', 'ò', 'ô', 'õ', 'á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ú'], ['o', 'o', 'o', 'o', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'u'], $normalizedName);

            $match = str_contains($fuzzyName, $fuzzySlug) || str_contains($fuzzySlug, $fuzzyName);
            echo "  Comparando slug='$fuzzySlug' com nome='$fuzzyName' => " . ($match ? 'MATCH' : 'no') . "\n";
            if ($match) {
                $resolved[] = $fullName;
            }
        }
    }
    
    echo "\nResolved EPIs: ";
    print_r(array_unique($resolved));
}
