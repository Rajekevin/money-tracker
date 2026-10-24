<?php

declare(strict_types=1);

namespace App\Application\Budget\Exception;

use App\Domain\Budget\Month;

final class BudgetAlreadyDefined extends \RuntimeException
{
    public static function forMonth(Month $month): self
    {
        return new self(sprintf('A budget is already defined for %s.', $month->toString()));
    }
}
