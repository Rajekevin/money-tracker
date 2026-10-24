<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Budget;

use App\Domain\Budget\BudgetStatus;
use App\Domain\Budget\Event\BudgetExceeded;
use App\Domain\Budget\Event\BudgetWarningReached;
use App\Domain\Budget\Exception\InvalidAmount;
use App\Domain\Budget\Exception\InvalidExpense;
use App\Domain\Budget\Expense;
use App\Domain\Budget\Money;
use App\Domain\Budget\Month;
use App\Domain\Budget\MonthlyBudget;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MonthlyBudgetTest extends TestCase
{
    #[Test]
    public function a_new_budget_is_on_track_with_everything_left(): void
    {
        $budget = $this->octoberBudgetOf('500');

        self::assertSame(BudgetStatus::OnTrack, $budget->status());
        self::assertSame(50000, $budget->remaining()->cents);
        self::assertSame([], $budget->releaseEvents());
    }

    #[Test]
    public function the_limit_must_be_positive(): void
    {
        $this->expectException(InvalidAmount::class);

        MonthlyBudget::define(Month::fromString('2026-10'), Money::zero());
    }

    #[Test]
    public function an_expense_below_the_warning_threshold_raises_nothing(): void
    {
        $budget = $this->octoberBudgetOf('500');

        $budget->record($this->expense('399.99', '2026-10-05'));

        self::assertSame(BudgetStatus::OnTrack, $budget->status());
        self::assertSame([], $budget->releaseEvents());
    }

    #[Test]
    public function reaching_80_percent_raises_a_warning_once(): void
    {
        $budget = $this->octoberBudgetOf('500');

        $budget->record($this->expense('400', '2026-10-05'));
        $budget->record($this->expense('10', '2026-10-06'));

        self::assertSame(BudgetStatus::Warning, $budget->status());
        $events = $budget->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(BudgetWarningReached::class, $events[0]);
    }

    #[Test]
    public function exceeding_the_limit_raises_one_alert_with_the_overspent_amount(): void
    {
        $budget = $this->octoberBudgetOf('500');

        $budget->record($this->expense('520', '2026-10-12'));
        $budget->record($this->expense('30', '2026-10-20'));

        self::assertSame(BudgetStatus::Exceeded, $budget->status());
        $events = $budget->releaseEvents();
        self::assertCount(1, $events, 'Jumping straight past the limit only raises the overspending alert.');
        self::assertInstanceOf(BudgetExceeded::class, $events[0]);
        self::assertSame(2000, $events[0]->overspent->cents);
    }

    #[Test]
    public function spending_exactly_the_limit_is_not_overspending(): void
    {
        $budget = $this->octoberBudgetOf('500');

        $budget->record($this->expense('500', '2026-10-12'));

        self::assertSame(BudgetStatus::Warning, $budget->status());
        self::assertSame(0, $budget->remaining()->cents);
    }

    #[Test]
    public function an_expense_from_another_month_is_refused(): void
    {
        $budget = $this->octoberBudgetOf('500');

        try {
            $budget->record($this->expense('40', '2026-11-03'));
            self::fail('The expense should have been refused.');
        } catch (InvalidExpense) {
            self::assertSame([], $budget->expenses());
        }
    }

    #[Test]
    public function a_restored_budget_does_not_raise_its_alerts_again(): void
    {
        $budget = MonthlyBudget::restore(
            Month::fromString('2026-10'),
            Money::fromEuros('500'),
            [$this->expense('520', '2026-10-12')],
            warningRaised: true,
            exceededRaised: true,
        );

        $budget->record($this->expense('10', '2026-10-13'));

        self::assertSame([], $budget->releaseEvents());
    }

    private function octoberBudgetOf(string $limit): MonthlyBudget
    {
        return MonthlyBudget::define(Month::fromString('2026-10'), Money::fromEuros($limit));
    }

    private function expense(string $amount, string $date): Expense
    {
        return new Expense(Money::fromEuros($amount), 'Courses', new \DateTimeImmutable($date));
    }
}
