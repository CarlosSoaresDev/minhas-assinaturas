<?php

$dir = 'f:/PROJETOS-DEV/gerenciamento-assinaturas/resources/views/pages/settings/';
$files = scandir($dir);

foreach ($files as $file) {
    if (str_contains($file, 'accounts') || str_contains($file, 'appearance') || str_contains($file, 'password') || str_contains($file, 'profile') || str_contains($file, 'two-factor')) {
        // Se o arquivo NÃO tem o raio, adiciona de volta
        if (!str_contains($file, '⚡')) {
            $newName = '⚡' . $file;
            echo "Restaurando: $file -> $newName\n";
            rename($dir . $file, $dir . $newName);
        }
    }
}
