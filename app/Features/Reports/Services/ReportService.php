<?php

namespace App\Features\Reports\Services;

use App\Features\Eclat\Services\EclatService;
use App\Features\Reports\Interfaces\ReportRepositoryInterface;

class ReportService
{
    public function __construct(
        private readonly ReportRepositoryInterface $reports,
        private readonly EclatService $eclat,
    ) {}

    public function summary(): array
    {
        $summary = $this->reports->summary();
        $summary['process_steps'] = $this->eclat->processSteps($summary['latest_run']);

        return $summary;
    }

    public function recommendations(int $limit = 5): array
    {
        return $this->reports->recommendations($limit);
    }
}
