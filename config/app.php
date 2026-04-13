<?php

return [
    'name' => 'Facchini',
    'env' => 'dev',
    'ldap' => [
        'enabled' => true,
        'mock_mode' => true, // Ativado para simulação sem servidor real
        'host' => '10.0.0.1',
        'port' => 389,
        'domain' => 'FACCHINI',
        'base_dn' => 'DC=facchini,DC=local',
        'user_lookup_field' => 'samaccountname',
        'mock_storage' => __DIR__ . '/../storage/mock_ad.json',
    ],
];
