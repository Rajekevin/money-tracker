<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Budget;

use App\Application\Budget\DefineMonthlyBudget\DefineMonthlyBudget;
use App\Application\Budget\DefineMonthlyBudget\DefineMonthlyBudgetHandler;
use App\Application\Budget\Exception\BudgetAlreadyDefined;
use App\Application\Budget\Exception\BudgetNotFound;
use App\Application\Budget\RecordExpense\RecordExpense;
use App\Application\Budget\RecordExpense\RecordExpenseHandler;
use App\Domain\Budget\Exception\InvalidExpense;
use App\Domain\Budget\Month;
use App\Infrastructure\Notification\RecordingAlertNotifier;
use App\Infrastructure\Persistence\InMemoryBudgetRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RecordExpenseHandlerTest extends TestCase
{
    private InMemoryBudgetRepository $budgets;

    private RecordingAlertNotifier $notifier;

    private RecordExpenseHandler $recordExpense;

    protected function setUp(): void
    {
        $this->budgets = new InMemoryBudgetRepository();
        $this->notifier = new RecordingAlertNotifier();
        $this->recordExpense = new RecordExpenseHandler($this->budgets, $this->notifier);

        (new DefineMonthlyBudgetHandler($this->budgets))(new DefineMonthlyBudget('2026-10', '500'));
    }

    #[Test]
    public function it_stores_the_expense_and_notifies_the_overspending(): void
    {
        ($this->recordExpense)(new RecordExpense('2026-10', '520', 'Réparation voiture', '2026-10-12'));

        $budget = $this->budgets->findForMonth(Month::fromString('2026-10'));
        self::assertNotNull($budget);
        self::assertCount(1, $budget->expenses());
        self::assertCount(1, $this->notifier->overspendingAlerts);
    }

    #[Test]
    public function it_fails_when_no_budget_exists_for_the_month(): void
    {
        $this->expectException(BudgetNotFound::class);

        ($this->recordExpense)(new RecordExpense('2026-11', '20', 'Cinéma', '2026-11-03'));
    }

    #[Test]
    public function it_rejects_an_impossible_date(): void
    {
        $this->expectException(InvalidExpense::class);

        ($this->recordExpense)(new RecordExpense('2026-10', '20', 'Cinéma', '2026-10-32'));
    }

    #[Test]
    public function a_month_cannot_get_two_budgets(): void
    {
        $this->expectException(BudgetAlreadyDefined::class);

        (new DefineMonthlyBudgetHandler($this->budgets))(new DefineMonthlyBudget('2026-10', '800'));
    }
}
