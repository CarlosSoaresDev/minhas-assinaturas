<?php

$dir = 'f:/PROJETOS-DEV/gerenciamento-assinaturas/resources/views/pages/settings/';
$files = scandir($dir);

foreach ($files as $file) {
    if (str_contains($file, 'accounts') || str_contains($file, 'appearance') || str_contains($file, 'password') || str_contains($file, 'profile') || str_contains($file, 'two-factor')) {
        // Remove emoji ou qualquer caractere não-ASCII do início
        $newName = preg_replace('/^[^\x20-\x7E]+/', '', $file);
        
        if ($file !== $newName) {
            echo "Renomeando: $file -> $newName\n";
            rename($dir . $file, $dir . $newName);
        }
    }
}
