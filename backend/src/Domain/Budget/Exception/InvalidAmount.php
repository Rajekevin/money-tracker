<?php

declare(strict_types=1);

namespace App\Domain\Budget\Exception;

final class InvalidAmount extends \DomainException
{
    public static function notANumber(string $value): self
    {
        return new self(sprintf('"%s" is not a valid amount in euros.', $value));
    }

    public static function mustBePositive(string $what): self
    {
        return new self(sprintf('%s must be greater than zero.', $what));
    }
}
