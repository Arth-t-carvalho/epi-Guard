<?php

namespace epiGuard\Presentation\Controller;

use epiGuard\Infrastructure\Persistence\PostgreSQLEpiRepository;

class DashboardController
{
    public function index()
    {
        $epiRepo = new PostgreSQLEpiRepository();
        $epis = $epiRepo->findAll();

        require_once __DIR__ . '/../View/dashboard/index.php';
    }
}

