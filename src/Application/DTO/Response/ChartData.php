<?php
declare(strict_types = 1)
;

namespace epiGuard\Application\DTO\Response;

class ChartData
{
    public array $labels = [];
    public array $datasets = [];

    public function __construct(array $labels = [], array $datasets = [])
    {
        $this->labels = $labels;
        $this->datasets = $datasets;
    }
}
