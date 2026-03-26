<?php

namespace epiGuard\Presentation\Controller\Api;

use epiGuard\Infrastructure\Persistence\MySQLOccurrenceRepository;
use epiGuard\Infrastructure\Persistence\MySQLEmployeeRepository;
use epiGuard\Infrastructure\Persistence\MySQLUserRepository;
use epiGuard\Infrastructure\Persistence\MySQLEpiRepository;
use epiGuard\Infrastructure\Persistence\MySQLDepartmentRepository;

class NotificationApiController
{
    private MySQLOccurrenceRepository $occurrenceRepository;

    public function __construct()
    {
        $deptRepo = new MySQLDepartmentRepository();
        $employeeRepo = new MySQLEmployeeRepository($deptRepo);
        $userRepo = new MySQLUserRepository();
        $epiRepo = new MySQLEpiRepository();
        $this->occurrenceRepository = new MySQLOccurrenceRepository($employeeRepo, $userRepo, $epiRepo);
    }

    public function check()
    {
        header('Content-Type: application/json');
        
        $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        
        // Se for a primeira carga, retorna o ID mais recente e os dados recentes (24h)
        if ($last_id === 0) {
            $all = $this->occurrenceRepository->findAll();
            $latestId = !empty($all) ? $all[0]->getId() : 0;
            
            // Buscar "novas" (últimas 24h) para inicializar o badge
            $recent = $this->occurrenceRepository->findNewInfractions(-1);
            
            echo json_encode([
                'status' => 'init', 
                'last_id' => $latestId,
                'dados' => $recent
            ]);
            return;
        }

        // Busca novas infrações reais
        $news = $this->occurrenceRepository->findNewInfractions($last_id);
        
        $last_id_new = $last_id;
        if (!empty($news)) {
            $last_id_new = end($news)['id'];
        }

        echo json_encode([
            'status' => 'success',
            'last_id' => $last_id_new,
            'dados' => $news
        ]);
    }
}
