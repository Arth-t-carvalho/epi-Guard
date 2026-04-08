<?php
$path1 = __DIR__ . '/src/Infrastructure/Persistence/MySQLOccurrenceRepository.php';
$content1 = file_get_contents($path1);

// Replace query clauses
$content1 = str_replace('AND f.filial_id = ?', '', $content1);

// the bind_param types string
$content1 = str_replace("'s' . str_repeat('i', count(\$sectorIds)) . 'ii'", "'s' . str_repeat('i', count(\$sectorIds)) . 'i'", $content1);
$content1 = str_replace("'ss' . str_repeat('i', count(\$sectorIds)) . 'ii'", "'ss' . str_repeat('i', count(\$sectorIds)) . 'i'", $content1);
$content1 = str_replace("'sii'", "'si'", $content1);
$content1 = str_replace("'ssii'", "'ssi'", $content1);
// for getMonthlyInfractionStats
$content1 = str_replace("\$finalTypes = \"iii\";", "\$finalTypes = \"ii\";", $content1);
$content1 = str_replace("\$totalTypes = \"iii\";", "\$totalTypes = \"ii\";", $content1);

// array merge replacements
$content1 = str_replace("[\$activeFilial, \$activeFilial]", "[\$activeFilial]", $content1);
$content1 = str_replace("\$dateStr, \$activeFilial, \$activeFilial", "\$dateStr, \$activeFilial", $content1);
$content1 = str_replace("\$dateStr, \$dateStr, \$activeFilial, \$activeFilial", "\$dateStr, \$dateStr, \$activeFilial", $content1);
$content1 = str_replace("\$startStr, \$endStr, \$activeFilial, \$activeFilial", "\$startStr, \$endStr, \$activeFilial", $content1);
$content1 = str_replace("\$year, \$activeFilial, \$activeFilial", "\$year, \$activeFilial", $content1);

file_put_contents($path1, $content1);
echo "Fixed repo.\\n";

$path2 = __DIR__ . '/src/Presentation/Controller/Api/OccurrenceApiController.php';
$content2 = file_get_contents($path2);
$content2 = str_replace('AND f.filial_id = ?', '', $content2);
$content2 = str_replace("['iiii' . str_repeat('i', count(\$sectorIds))]", "['iii' . str_repeat('i', count(\$sectorIds))]", $content2); // if any
$content2 = str_replace("\$types = 'iiii' . str_repeat('i', count(\$sectorIds));", "\$types = 'iii' . str_repeat('i', count(\$sectorIds));", $content2);
$content2 = str_replace("[\$month, \$year, \$activeFilial, \$activeFilial]", "[\$month, \$year, \$activeFilial]", $content2);
$content2 = str_replace("bind_param('iiii'", "bind_param('iii'", $content2);
$content2 = str_replace("\$month, \$year, \$activeFilial, \$activeFilial", "\$month, \$year, \$activeFilial", $content2);
file_put_contents($path2, $content2);
echo "Fixed api.\\n";
