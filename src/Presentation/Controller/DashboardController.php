<?php

namespace epiGuard\Presentation\Controller;

use epiGuard\Infrastructure\Persistence\MySQLEpiRepository;

class DashboardController
{
    public function index()
    {
        $epiRepo = new MySQLEpiRepository();
        $epis = $epiRepo->findAll();

        require_once __DIR__ . '/../View/dashboard/index.php';
    }
}
