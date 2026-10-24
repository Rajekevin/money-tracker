<?php

declare(strict_types=1);

namespace App\Domain\Budget;

use App\Domain\Budget\Exception\InvalidAmount;
use App\Domain\Budget\Exception\InvalidExpense;

final readonly class Expense
{
    public function __construct(
        public Money $amount,
        public string $label,
        public \DateTimeImmutable $spentOn,
    ) {
        if (!$amount->isPositive()) {
            throw InvalidAmount::mustBePositive('An expense');
        }

        if ('' === trim($label)) {
            throw InvalidExpense::missingLabel();
        }
    }
}
