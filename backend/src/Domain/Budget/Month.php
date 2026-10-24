<?php

declare(strict_types=1);

namespace App\Domain\Budget;

use App\Domain\Budget\Exception\InvalidMonth;

/**
 * A calendar month such as "2026-10". A budget covers exactly one month.
 */
final readonly class Month
{
    private function __construct(
        public int $year,
        public int $month,
    ) {
    }

    public static function fromString(string $value): self
    {
        if (1 !== preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $value, $parts)) {
            throw InvalidMonth::badFormat($value);
        }

        return new self((int) $parts[1], (int) $parts[2]);
    }

    public static function of(\DateTimeImmutable $date): self
    {
        return new self((int) $date->format('Y'), (int) $date->format('n'));
    }

    public function contains(\DateTimeImmutable $date): bool
    {
        return $this->equals(self::of($date));
    }

    public function equals(self $other): bool
    {
        return $this->year === $other->year && $this->month === $other->month;
    }

    public function toString(): string
    {
        return sprintf('%04d-%02d', $this->year, $this->month);
    }
}
