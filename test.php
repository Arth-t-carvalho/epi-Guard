<?php
require 'c:\xampp\htdocs\epi-Guard\src\Infrastructure\i18n.php';
$_COOKIE['epiguard-lang'] = 'en';

$str = "window.I18N = {\n";
$str .= "  months: ['" . __('Janeiro') . "'],\n";
$str .= "  test: '" . __('Nenhum setor selecionado. Por favor, escolha um setor acima para carregar a lista.') . "'\n";
$str .= "};\n";

echo $str;
