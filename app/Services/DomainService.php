<?php

namespace App\Services;

use App\Models\Domain;

class DomainService
{
    public function __construct(private WhoisService $whoisService) {}

    public function create(array $data, string $privacyToken): Domain
    {
        $data['privacy_token'] = $privacyToken;

        if (empty($data['expiration_date']) || empty($data['registrar'])) {
            $whoisData = $this->whoisService->lookup($data['domain_name']);

            if (!empty($whoisData)) {
                $data['expiration_date'] = $whoisData['expiration_date'] ?? $data['expiration_date'];
                $data['registrar'] = $whoisData['registrar'] ?? $data['registrar'];
                $data['registration_date'] = $whoisData['registration_date'] ?? $data['registration_date'];
            }
        }

        return Domain::create($data);
    }
}
