<?php

declare(strict_types=1);

namespace App\Domain\Budget;

/**
 * Port: where monthly budgets are kept. Adapters live in Infrastructure.
 */
interface BudgetRepository
{
    public function save(MonthlyBudget $budget): void;

    public function findForMonth(Month $month): ?MonthlyBudget;
}
