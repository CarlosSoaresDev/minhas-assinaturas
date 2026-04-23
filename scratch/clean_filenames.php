<?php

$dir = 'f:/PROJETOS-DEV/gerenciamento-assinaturas/resources/views/pages/settings/';
$files = scandir($dir);

foreach ($files as $file) {
    if (str_contains($file, '⚡')) {
        $newName = str_replace('⚡', '', $file);
        echo "Renomeando: $file -> $newName\n";
        if (file_exists($dir . $newName)) {
            unlink($dir . $newName); // Remove se já existir um sem raio para evitar conflito
        }
        rename($dir . $file, $dir . $newName);
    }
}
