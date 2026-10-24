<?php

declare(strict_types=1);

namespace App\Application\Budget\GetBudgetSummary;

use App\Domain\Budget\MonthlyBudget;

/**
 * Read model returned to the outside world. Amounts are decimal strings in euros.
 */
final readonly class BudgetSummary
{
    public function __construct(
        public string $month,
        public string $limit,
        public string $spent,
        public string $remaining,
        public string $status,
        public int $expenseCount,
    ) {
    }

    public static function of(MonthlyBudget $budget): self
    {
        return new self(
            $budget->month->toString(),
            $budget->limit->toDecimalString(),
            $budget->spent()->toDecimalString(),
            $budget->remaining()->toDecimalString(),
            $budget->status()->value,
            \count($budget->expenses()),
        );
    }
}
