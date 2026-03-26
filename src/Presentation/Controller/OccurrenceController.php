<?php

namespace epiGuard\Presentation\Controller;

class OccurrenceController
{
    public function index()
    {
        require_once __DIR__ . '/../View/occurrences/list.php';
    }
}
