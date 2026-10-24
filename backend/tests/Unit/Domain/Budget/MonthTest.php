<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Budget;

use App\Domain\Budget\Exception\InvalidMonth;
use App\Domain\Budget\Month;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class MonthTest extends TestCase
{
    #[Test]
    public function it_contains_only_the_days_of_that_month(): void
    {
        $october = Month::fromString('2026-10');

        self::assertTrue($october->contains(new \DateTimeImmutable('2026-10-01')));
        self::assertTrue($october->contains(new \DateTimeImmutable('2026-10-31')));
        self::assertFalse($october->contains(new \DateTimeImmutable('2026-11-01')));
        self::assertFalse($october->contains(new \DateTimeImmutable('2025-10-15')));
    }

    #[Test]
    #[TestWith(['2026-13'])]
    #[TestWith(['2026-1'])]
    #[TestWith(['octobre'])]
    public function it_rejects_badly_formatted_months(string $value): void
    {
        $this->expectException(InvalidMonth::class);

        Month::fromString($value);
    }

    #[Test]
    public function it_round_trips_to_string(): void
    {
        self::assertSame('2026-03', Month::fromString('2026-03')->toString());
    }
}
