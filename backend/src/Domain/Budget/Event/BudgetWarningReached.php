<?php

declare(strict_types=1);

namespace App\Domain\Budget\Event;

use App\Domain\Budget\Money;
use App\Domain\Budget\Month;

final readonly class BudgetWarningReached
{
    public function __construct(
        public Month $month,
        public Money $spent,
        public Money $limit,
    ) {
    }
}
