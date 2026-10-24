<?php

declare(strict_types=1);

namespace App\Application\Budget\RecordExpense;

use App\Application\Budget\Exception\BudgetNotFound;
use App\Application\Budget\Port\AlertNotifier;
use App\Domain\Budget\BudgetRepository;
use App\Domain\Budget\Event\BudgetExceeded;
use App\Domain\Budget\Event\BudgetWarningReached;
use App\Domain\Budget\Exception\InvalidExpense;
use App\Domain\Budget\Expense;
use App\Domain\Budget\Money;
use App\Domain\Budget\Month;

final readonly class RecordExpenseHandler
{
    public function __construct(
        private BudgetRepository $budgets,
        private AlertNotifier $notifier,
    ) {
    }

    public function __invoke(RecordExpense $command): void
    {
        $month = Month::fromString($command->month);
        $budget = $this->budgets->findForMonth($month) ?? throw BudgetNotFound::forMonth($month);

        $spentOn = \DateTimeImmutable::createFromFormat('!Y-m-d', $command->spentOn);
        if (false === $spentOn || $spentOn->format('Y-m-d') !== $command->spentOn) {
            throw InvalidExpense::invalidDate($command->spentOn);
        }

        $budget->record(new Expense(Money::fromEuros($command->amount), $command->label, $spentOn));
        $this->budgets->save($budget);

        // Notify only once the expense is safely stored.
        foreach ($budget->releaseEvents() as $event) {
            match (true) {
                $event instanceof BudgetExceeded => $this->notifier->alertOverspending($event),
                $event instanceof BudgetWarningReached => $this->notifier->warnApproachingLimit($event),
            };
        }
    }
}
