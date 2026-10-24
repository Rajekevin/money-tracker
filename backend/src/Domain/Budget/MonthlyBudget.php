<?php

declare(strict_types=1);

namespace App\Domain\Budget;

use App\Domain\Budget\Event\BudgetExceeded;
use App\Domain\Budget\Event\BudgetWarningReached;
use App\Domain\Budget\Exception\InvalidAmount;
use App\Domain\Budget\Exception\InvalidExpense;

/**
 * Aggregate root: the spending limit of one month and the expenses recorded against it.
 *
 * Invariants:
 * - every expense belongs to the budget's month;
 * - the warning and the overspending alert are each raised at most once per month.
 */
final class MonthlyBudget
{
    public const int WARNING_THRESHOLD_PERCENT = 80;

    /** @var list<Expense> */
    private array $expenses = [];

    private bool $warningRaised = false;

    private bool $exceededRaised = false;

    /** @var list<BudgetWarningReached|BudgetExceeded> */
    private array $pendingEvents = [];

    private function __construct(
        public readonly Month $month,
        public readonly Money $limit,
    ) {
        if (!$limit->isPositive()) {
            throw InvalidAmount::mustBePositive('A monthly budget');
        }
    }

    public static function define(Month $month, Money $limit): self
    {
        return new self($month, $limit);
    }

    /**
     * Rebuilds a budget from storage without raising any event again.
     *
     * @param list<Expense> $expenses
     */
    public static function restore(Month $month, Money $limit, array $expenses, bool $warningRaised, bool $exceededRaised): self
    {
        $budget = new self($month, $limit);
        $budget->expenses = $expenses;
        $budget->warningRaised = $warningRaised;
        $budget->exceededRaised = $exceededRaised;

        return $budget;
    }

    public function record(Expense $expense): void
    {
        if (!$this->month->contains($expense->spentOn)) {
            throw InvalidExpense::outsideMonth($expense->spentOn, $this->month);
        }

        $this->expenses[] = $expense;

        $status = $this->status();

        if (BudgetStatus::Exceeded === $status && !$this->exceededRaised) {
            $this->exceededRaised = true;
            // Crossing both thresholds at once only deserves the stronger alert.
            $this->warningRaised = true;
            $this->pendingEvents[] = new BudgetExceeded($this->month, $this->remaining()->absolute(), $this->limit);

            return;
        }

        if (BudgetStatus::Warning === $status && !$this->warningRaised) {
            $this->warningRaised = true;
            $this->pendingEvents[] = new BudgetWarningReached($this->month, $this->spent(), $this->limit);
        }
    }

    public function spent(): Money
    {
        return array_reduce(
            $this->expenses,
            static fn (Money $total, Expense $expense): Money => $total->add($expense->amount),
            Money::zero(),
        );
    }

    /**
     * Negative when the limit is exceeded.
     */
    public function remaining(): Money
    {
        return $this->limit->subtract($this->spent());
    }

    public function status(): BudgetStatus
    {
        $spent = $this->spent();

        if ($spent->isGreaterThan($this->limit)) {
            return BudgetStatus::Exceeded;
        }

        if ($spent->isGreaterThanOrEqual($this->limit->percentage(self::WARNING_THRESHOLD_PERCENT))) {
            return BudgetStatus::Warning;
        }

        return BudgetStatus::OnTrack;
    }

    /**
     * @return list<Expense>
     */
    public function expenses(): array
    {
        return $this->expenses;
    }

    public function warningRaised(): bool
    {
        return $this->warningRaised;
    }

    public function exceededRaised(): bool
    {
        return $this->exceededRaised;
    }

    /**
     * Hands over the events raised since the last call, then forgets them.
     *
     * @return list<BudgetWarningReached|BudgetExceeded>
     */
    public function releaseEvents(): array
    {
        $events = $this->pendingEvents;
        $this->pendingEvents = [];

        return $events;
    }
}
