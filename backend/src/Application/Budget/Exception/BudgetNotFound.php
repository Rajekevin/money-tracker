<?php

declare(strict_types=1);

namespace App\Application\Budget\Exception;

use App\Domain\Budget\Month;

final class BudgetNotFound extends \RuntimeException
{
    public static function forMonth(Month $month): self
    {
        return new self(sprintf('No budget is defined for %s.', $month->toString()));
    }
}
