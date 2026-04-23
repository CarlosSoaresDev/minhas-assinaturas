<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordSecurityService
{
    /**
     * Aplica sal e pimenta na senha seguindo os requisitos de segurança.
     * Sal: adicionado antes de encriptar.
     * Pimenta: adicionada após encriptar.
     */
    public static function hashPassword(string $plainPassword): string
    {
        $salt = config('app.password_salt');
        $pepper = config('app.password_pepper');

        // 1. Soma o sal à senha
        $saltedPassword = $plainPassword . $salt;

        // 2. Encripta
        $hashed = Hash::make($saltedPassword);

        // 3. Adiciona a pimenta ao final do hash
        return $hashed . $pepper;
    }

    /**
     * Verifica a senha separando a pimenta e validando o sal.
     */
    public static function checkPassword(string $plainPassword, string $storedHashWithPepper): bool
    {
        $salt = config('app.password_salt');
        $pepper = config('app.password_pepper');

        // Se por acaso a senha não tiver a pimenta (usuário antigo), cai no fallback padrão do Laravel
        if (!Str::endsWith($storedHashWithPepper, $pepper)) {
            return Hash::check($plainPassword, $storedHashWithPepper);
        }

        // 1. Remove a pimenta do final da string
        $originalHash = Str::replaceLast($pepper, '', $storedHashWithPepper);

        // 2. Soma o sal à senha recebida
        $saltedPassword = $plainPassword . $salt;

        // 3. Verifica contra o hash original
        return Hash::check($saltedPassword, $originalHash);
    }
}
