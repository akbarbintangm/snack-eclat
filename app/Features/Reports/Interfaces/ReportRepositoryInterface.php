<?php

namespace App\Features\Reports\Interfaces;

interface ReportRepositoryInterface
{
    public function summary(): array;

    public function recommendations(int $limit = 5): array;
}
