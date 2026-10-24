<?php

declare(strict_types=1);

namespace App\Domain\Budget\Exception;

final class InvalidMonth extends \DomainException
{
    public static function badFormat(string $value): self
    {
        return new self(sprintf('"%s" is not a month. Expected the YYYY-MM format.', $value));
    }
}
