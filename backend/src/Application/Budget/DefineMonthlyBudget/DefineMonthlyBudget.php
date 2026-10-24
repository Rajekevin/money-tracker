<?php

declare(strict_types=1);

namespace App\Application\Budget\DefineMonthlyBudget;

final readonly class DefineMonthlyBudget
{
    /**
     * @param string $month YYYY-MM
     * @param string $limit amount in euros, e.g. "500" or "500.00"
     */
    public function __construct(
        public string $month,
        public string $limit,
    ) {
    }
}
