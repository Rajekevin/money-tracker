<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Budget\BudgetRepository;
use App\Domain\Budget\Expense;
use App\Domain\Budget\Money;
use App\Domain\Budget\Month;
use App\Domain\Budget\MonthlyBudget;

/**
 * Simple adapter for local use: one JSON file holding every budget.
 * A Doctrine adapter is the next step (see README).
 *
 * @phpstan-type ExpenseRow array{cents: int, label: string, spentOn: string}
 * @phpstan-type BudgetRow array{limitCents: int, warningRaised: bool, exceededRaised: bool, expenses: list<ExpenseRow>}
 */
final readonly class JsonFileBudgetRepository implements BudgetRepository
{
    public function __construct(private string $path)
    {
    }

    public function save(MonthlyBudget $budget): void
    {
        $rows = $this->read();
        $rows[$budget->month->toString()] = [
            'limitCents' => $budget->limit->cents,
            'warningRaised' => $budget->warningRaised(),
            'exceededRaised' => $budget->exceededRaised(),
            'expenses' => array_map(
                static fn (Expense $expense): array => [
                    'cents' => $expense->amount->cents,
                    'label' => $expense->label,
                    'spentOn' => $expense->spentOn->format('Y-m-d'),
                ],
                $budget->expenses(),
            ),
        ];

        $directory = \dirname($this->path);
        if (!is_dir($directory) && !mkdir($directory, 0o775, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Cannot create the "%s" directory.', $directory));
        }

        file_put_contents($this->path, json_encode($rows, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR), \LOCK_EX);
    }

    public function findForMonth(Month $month): ?MonthlyBudget
    {
        $row = $this->read()[$month->toString()] ?? null;

        if (null === $row) {
            return null;
        }

        return MonthlyBudget::restore(
            $month,
            Money::ofCents($row['limitCents']),
            array_map(
                static fn (array $expense): Expense => new Expense(
                    Money::ofCents($expense['cents']),
                    $expense['label'],
                    new \DateTimeImmutable($expense['spentOn']),
                ),
                $row['expenses'],
            ),
            $row['warningRaised'],
            $row['exceededRaised'],
        );
    }

    /**
     * @return array<string, BudgetRow>
     */
    private function read(): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        /** @var array<string, BudgetRow> $rows */
        $rows = json_decode((string) file_get_contents($this->path), true, 512, \JSON_THROW_ON_ERROR);

        return $rows;
    }
}
