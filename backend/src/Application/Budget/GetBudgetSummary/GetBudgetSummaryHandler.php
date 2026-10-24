<?php

declare(strict_types=1);

namespace App\Application\Budget\GetBudgetSummary;

use App\Application\Budget\Exception\BudgetNotFound;
use App\Domain\Budget\BudgetRepository;
use App\Domain\Budget\Month;

final readonly class GetBudgetSummaryHandler
{
    public function __construct(private BudgetRepository $budgets)
    {
    }

    public function __invoke(GetBudgetSummary $query): BudgetSummary
    {
        $month = Month::fromString($query->month);
        $budget = $this->budgets->findForMonth($month) ?? throw BudgetNotFound::forMonth($month);

        return BudgetSummary::of($budget);
    }
}
