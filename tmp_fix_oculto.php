<?php
$path = __DIR__ . '/src/Infrastructure/Persistence/MySQLOccurrenceRepository.php';
$content = file_get_contents($path);

// We need to add AND o.oculto = FALSE before AND o.filial_id = ?
// The pattern is: AND o.tipo = 'INFRACAO' AND o.filial_id = ?
$newContent = str_replace(
    "AND o.tipo = 'INFRACAO' AND o.filial_id = ?",
    "AND o.tipo = 'INFRACAO' AND o.oculto = FALSE AND o.filial_id = ?",
    $content
);

file_put_contents($path, $newContent);
echo "Fixed missing oculto=FALSE in MySQLOccurrenceRepository!";
