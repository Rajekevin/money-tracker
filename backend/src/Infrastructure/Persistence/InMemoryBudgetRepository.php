<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Budget\BudgetRepository;
use App\Domain\Budget\Month;
use App\Domain\Budget\MonthlyBudget;

/**
 * Adapter used by the Behat scenarios and the test environment.
 */
final class InMemoryBudgetRepository implements BudgetRepository
{
    /** @var array<string, MonthlyBudget> */
    private array $budgets = [];

    public function save(MonthlyBudget $budget): void
    {
        $this->budgets[$budget->month->toString()] = $budget;
    }

    public function findForMonth(Month $month): ?MonthlyBudget
    {
        return $this->budgets[$month->toString()] ?? null;
    }
}
