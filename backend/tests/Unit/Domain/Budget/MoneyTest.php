<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Budget;

use App\Domain\Budget\Exception\InvalidAmount;
use App\Domain\Budget\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    /**
     * @return iterable<string, array{string, int}>
     */
    public static function validAmounts(): iterable
    {
        yield 'whole euros' => ['520', 52000];
        yield 'dot decimals' => ['520.5', 52050];
        yield 'comma decimals' => ['520,50', 52050];
        yield 'thousands separator' => ['1 234,56', 123456];
        yield 'negative' => ['-20', -2000];
        yield 'one cent' => ['0.01', 1];
    }

    #[Test]
    #[DataProvider('validAmounts')]
    public function it_parses_euros_into_cents_without_floats(string $euros, int $expectedCents): void
    {
        self::assertSame($expectedCents, Money::fromEuros($euros)->cents);
    }

    #[Test]
    public function it_rejects_text_that_is_not_an_amount(): void
    {
        $this->expectException(InvalidAmount::class);

        Money::fromEuros('vingt euros');
    }

    #[Test]
    public function it_rejects_more_than_two_decimals(): void
    {
        $this->expectException(InvalidAmount::class);

        Money::fromEuros('10.999');
    }

    #[Test]
    public function it_adds_and_subtracts(): void
    {
        $result = Money::fromEuros('500')->subtract(Money::fromEuros('520'));

        self::assertSame(-2000, $result->cents);
        self::assertTrue($result->isNegative());
        self::assertSame(2000, $result->absolute()->cents);
    }

    #[Test]
    public function percentage_rounds_down(): void
    {
        self::assertSame(33, Money::ofCents(99)->percentage(34)->cents);
    }

    #[Test]
    public function it_formats_as_a_decimal_string(): void
    {
        self::assertSame('380.00', Money::fromEuros('380')->toDecimalString());
        self::assertSame('-0.05', Money::ofCents(-5)->toDecimalString());
    }
}
