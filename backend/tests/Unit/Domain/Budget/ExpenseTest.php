<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Budget;

use App\Domain\Budget\Exception\InvalidAmount;
use App\Domain\Budget\Exception\InvalidExpense;
use App\Domain\Budget\Expense;
use App\Domain\Budget\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExpenseTest extends TestCase
{
    #[Test]
    public function an_expense_must_cost_something(): void
    {
        $this->expectException(InvalidAmount::class);

        new Expense(Money::zero(), 'Courses', new \DateTimeImmutable('2026-10-05'));
    }

    #[Test]
    public function an_expense_needs_a_label(): void
    {
        $this->expectException(InvalidExpense::class);

        new Expense(Money::fromEuros('12'), '   ', new \DateTimeImmutable('2026-10-05'));
    }
}
