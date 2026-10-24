<?php

declare(strict_types=1);

namespace App\Application\Budget\DefineMonthlyBudget;

use App\Application\Budget\Exception\BudgetAlreadyDefined;
use App\Domain\Budget\BudgetRepository;
use App\Domain\Budget\Money;
use App\Domain\Budget\Month;
use App\Domain\Budget\MonthlyBudget;

final readonly class DefineMonthlyBudgetHandler
{
    public function __construct(private BudgetRepository $budgets)
    {
    }

    public function __invoke(DefineMonthlyBudget $command): void
    {
        $month = Month::fromString($command->month);

        if (null !== $this->budgets->findForMonth($month)) {
            throw BudgetAlreadyDefined::forMonth($month);
        }

        $this->budgets->save(MonthlyBudget::define($month, Money::fromEuros($command->limit)));
    }
}
