<?php

declare(strict_types=1);

namespace App\Application\Budget\RecordExpense;

final readonly class RecordExpense
{
    /**
     * @param string $month   YYYY-MM
     * @param string $amount  amount in euros, e.g. "120" or "120.50"
     * @param string $spentOn YYYY-MM-DD
     */
    public function __construct(
        public string $month,
        public string $amount,
        public string $label,
        public string $spentOn,
    ) {
    }
}
