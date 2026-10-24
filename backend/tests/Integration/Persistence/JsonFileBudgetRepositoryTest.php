<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Domain\Budget\Expense;
use App\Domain\Budget\Money;
use App\Domain\Budget\Month;
use App\Domain\Budget\MonthlyBudget;
use App\Infrastructure\Persistence\JsonFileBudgetRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonFileBudgetRepositoryTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir().'/money-tracker-'.bin2hex(random_bytes(4)).'/budgets.json';
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
            rmdir(\dirname($this->path));
        }
    }

    #[Test]
    public function a_saved_budget_comes_back_identical(): void
    {
        $budget = MonthlyBudget::define(Month::fromString('2026-10'), Money::fromEuros('500'));
        $budget->record(new Expense(Money::fromEuros('520'), 'Réparation voiture', new \DateTimeImmutable('2026-10-12')));

        (new JsonFileBudgetRepository($this->path))->save($budget);
        $restored = (new JsonFileBudgetRepository($this->path))->findForMonth(Month::fromString('2026-10'));

        self::assertNotNull($restored);
        self::assertSame(-2000, $restored->remaining()->cents);
        self::assertTrue($restored->exceededRaised());
        self::assertSame('Réparation voiture', $restored->expenses()[0]->label);
    }

    #[Test]
    public function an_unknown_month_returns_nothing(): void
    {
        self::assertNull((new JsonFileBudgetRepository($this->path))->findForMonth(Month::fromString('2026-10')));
    }
}
