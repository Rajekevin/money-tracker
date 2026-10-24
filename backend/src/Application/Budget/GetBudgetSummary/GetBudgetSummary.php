<?php

declare(strict_types=1);

namespace App\Application\Budget\GetBudgetSummary;

final readonly class GetBudgetSummary
{
    /**
     * @param string $month YYYY-MM
     */
    public function __construct(public string $month)
    {
    }
}
