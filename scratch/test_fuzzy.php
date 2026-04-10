<?php
$name = 'Óculos de Proteção';
$slug = 'oculos';

echo "Original: $name\n";

$lower = mb_strtolower($name, 'UTF-8');
echo "mb_strtolower: $lower\n";

$fuzzy = str_replace(
    ['ó', 'ò', 'ô', 'õ', 'á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ú', 'ç'],
    ['o', 'o', 'o', 'o', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'u', 'c'],
    $lower
);
echo "Fuzzy: $fuzzy\n";
echo "str_contains('$fuzzy', '$slug') => " . (str_contains($fuzzy, $slug) ? 'MATCH' : 'no') . "\n";
