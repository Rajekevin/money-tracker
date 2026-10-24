<?php

declare(strict_types=1);

namespace App\Domain\Budget;

use App\Domain\Budget\Exception\InvalidAmount;

/**
 * An amount in euros, stored in cents to avoid float rounding.
 * It can be negative: a remaining amount below zero means overspending.
 */
final readonly class Money
{
    private function __construct(public int $cents)
    {
    }

    public static function ofCents(int $cents): self
    {
        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * Parses "520", "520.5", "520,50" or "1 234,56" without going through floats.
     */
    public static function fromEuros(string $euros): self
    {
        $normalized = str_replace([' ', "\u{00A0}", "\u{202F}", ','], ['', '', '', '.'], trim($euros));

        if (1 !== preg_match('/^(-)?(\d+)(?:\.(\d{1,2}))?$/', $normalized, $parts)) {
            throw InvalidAmount::notANumber($euros);
        }

        $cents = (int) $parts[2] * 100 + (int) str_pad($parts[3] ?? '0', 2, '0');

        return new self('-' === $parts[1] ? -$cents : $cents);
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function subtract(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function isPositive(): bool
    {
        return $this->cents > 0;
    }

    public function isNegative(): bool
    {
        return $this->cents < 0;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->cents > $other->cents;
    }

    public function isGreaterThanOrEqual(self $other): bool
    {
        return $this->cents >= $other->cents;
    }

    public function absolute(): self
    {
        return new self(abs($this->cents));
    }

    /**
     * Rounds down, so a warning is never raised later than announced.
     */
    public function percentage(int $percent): self
    {
        return new self(intdiv($this->cents * $percent, 100));
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }

    public function toDecimalString(): string
    {
        $sign = $this->cents < 0 ? '-' : '';
        $absolute = abs($this->cents);

        return sprintf('%s%d.%02d', $sign, intdiv($absolute, 100), $absolute % 100);
    }
}
