<?php

declare(strict_types=1);

namespace App\Domain\Budget\Exception;

use App\Domain\Budget\Month;

final class InvalidExpense extends \DomainException
{
    public static function missingLabel(): self
    {
        return new self('An expense needs a label.');
    }

    public static function invalidDate(string $value): self
    {
        return new self(sprintf('"%s" is not a valid date. Expected YYYY-MM-DD.', $value));
    }

    public static function outsideMonth(\DateTimeImmutable $spentOn, Month $month): self
    {
        return new self(sprintf(
            'An expense spent on %s does not belong to the %s budget.',
            $spentOn->format('Y-m-d'),
            $month->toString(),
        ));
    }
}
