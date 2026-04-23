<?php

namespace App\Services;

use Exception;

class WhoisService
{
    /**
     * Simula busca WHOIS (substituir por lib real ou cURL API).
     * @param string $domain
     * @return array
     */
    public function lookup(string $domain): array
    {
        // Se quisermos conectar real:
        // exec("whois " . escapeshellarg($domain), $output);
        // Ou usar json de uma api: file_get_contents("https://api.ip2whois.com/v2?key=demo&domain=".$domain);
        
        // Retornando mock local robusto de fallback temporário
        if (str_ends_with($domain, '.com')) {
            return [
                'registrar' => 'Godaddy',
                'registration_date' => now()->subYears(2)->format('Y-m-d'),
                'expiration_date' => now()->addMonths(6)->format('Y-m-d'),
            ];
        }

        return []; // Falha silenciosa = preenchimento manual no CRUD
    }
}
