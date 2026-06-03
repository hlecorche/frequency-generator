<?php

declare(strict_types=1);

/*
 * This file is part of the frequency-generator package.
 *
 * (c) E-commit <contact@e-commit.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecommit\FrequencyGenerator\Tests;

use Ecommit\FrequencyGenerator\FrequencyGenerator;
use PHPUnit\Framework\TestCase;

class FrequencyGeneratorTest extends TestCase
{
    public function testGenerateDateTimeImmutableDefault(): void
    {
        $generator = $this->createFrequencyGenerator('2020-10-01 15:00:00');

        $reflection = new \ReflectionClass(FrequencyGenerator::class);
        $generateDateTimeImmutableProperty = $reflection->getProperty('generateDateTimeImmutable');
        $generateDateTimeImmutableProperty->setAccessible(true);

        $this->assertFalse($generateDateTimeImmutableProperty->getValue($generator));
    }

    /**
     * @dataProvider getTestGenerateDateTimeImmutableProvider
     */
    public function testGenerateDateTimeImmutable(bool $value): void
    {
        $generator = $this->createFrequencyGenerator('2020-10-01 15:00:00');

        $reflection = new \ReflectionClass(FrequencyGenerator::class);
        $generateDateTimeImmutableProperty = $reflection->getProperty('generateDateTimeImmutable');
        $generateDateTimeImmutableProperty->setAccessible(true);

        $result = $generator->generateDateTimeImmutable($value);
        $this->assertInstanceOf(FrequencyGenerator::class, $result);
        $this->assertSame($value, $generateDateTimeImmutableProperty->getValue($generator));
    }

    /**
     * @return array<array{bool}>
     */
    public function getTestGenerateDateTimeImmutableProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testNextInEveryDayEmpty(): void
    {
        $generator = $this->createFrequencyGenerator('2020-10-01 15:00:00');

        $result = $generator->nextInEveryDay();
        $this->checkResultDate('2020-10-02 00:00:00', \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryDay();
        $this->checkResultDate('2020-10-02 00:00:00', \DateTimeImmutable::class, $result);
    }

    /**
     * @param string[] $times
     *
     * @dataProvider getTestNextInEveryDayProvider
     */
    public function testNextInEveryDay(string $now, array $times, string $expected): void
    {
        $generator = $this->createFrequencyGenerator($now);

        $result = $generator->nextInEveryDay($this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $result = $generator->nextInEveryDay($this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryDay($this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);

        $result = $generator->nextInEveryDay($this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);
    }

    /**
     * @return array<array{string, string[], string}>
     */
    public function getTestNextInEveryDayProvider(): array
    {
        return [
            ['2020-10-01 15:00:00', [], '2020-10-02 00:00:00'],
            ['2020-10-01 15:00:00', ['22:10:20'], '2020-10-01 22:10:20'],
            ['2020-10-01 15:00:00', ['22:10:20', '09:10:20', '00:10:20'], '2020-10-01 22:10:20'],
            ['2020-10-01 15:00:00', ['12:10:20', '09:10:20', '00:10:20'], '2020-10-02 00:10:20'],
        ];
    }

    public function testNextInEveryDayWithBadTime(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Times must be DateTimeInterface objects');

        $generator->nextInEveryDay(['fake']); // @phpstan-ignore-line
    }

    public function testNextInEveryWeekEmpty(): void
    {
        $generator = $this->createFrequencyGenerator('2020-10-01 15:00:00');

        $result = $generator->nextInEveryWeek();
        $this->checkResultDate('2020-10-05 00:00:00', \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryWeek();
        $this->checkResultDate('2020-10-05 00:00:00', \DateTimeImmutable::class, $result);
    }

    /**
     * @param array<int|string> $days
     * @param string[]          $times
     *
     * @dataProvider getTestNextInEveryWeekProvider
     */
    public function testNextInEveryWeek(string $now, array $days, array $times, string $expected): void
    {
        $generator = $this->createFrequencyGenerator($now);

        $result = $generator->nextInEveryWeek($days, $this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $result = $generator->nextInEveryWeek($days, $this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryWeek($days, $this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);

        $result = $generator->nextInEveryWeek($days, $this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);
    }

    /**
     * @return array<array{string, array<int|string>, string[], string}>
     */
    public function getTestNextInEveryWeekProvider(): array
    {
        return [
            ['2020-10-01 15:00:00', [], [], '2020-10-05 00:00:00'],
            ['2020-10-01 15:00:00', [2, 6], [], '2020-10-03 00:00:00'],
            ['2020-10-01 15:00:00', [2, 4, 6], ['22:10:20'], '2020-10-01 22:10:20'],
            ['2020-10-01 15:00:00', [2, 4, 6], ['13:10:20', '12:10:20', '14:10:20'], '2020-10-03 12:10:20'],

            // Next month
            ['2020-10-28 15:00:00', [], [], '2020-11-02 00:00:00'],

            // Next year
            ['2020-12-29 15:00:00', [], [], '2021-01-04 00:00:00'],

            ['2020-10-01 15:00:00', ['2', '6'], [], '2020-10-03 00:00:00'],
        ];
    }

    public function testNextInEveryWeekWithBadDay(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad day 8');

        $generator->nextInEveryWeek([8], []);
    }

    public function testNextInEveryWeekWithBadDayNotNumber(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad day fake');

        $generator->nextInEveryWeek(['fake'], []);
    }

    public function testNextInEveryWeekWithBadTime(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Times must be DateTimeInterface objects');

        $generator->nextInEveryWeek([], ['fake']); // @phpstan-ignore-line
    }

    public function testNextInEveryMonthEmpty(): void
    {
        $generator = $this->createFrequencyGenerator('2020-10-01 15:00:00');

        $result = $generator->nextInEveryMonth();
        $this->checkResultDate('2020-11-01 00:00:00', \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryMonth();
        $this->checkResultDate('2020-11-01 00:00:00', \DateTimeImmutable::class, $result);
    }

    /**
     * @param array<int|string> $days
     * @param string[]          $times
     *
     * @dataProvider getTestNextInEveryMonthProvider
     */
    public function testNextInEveryMonth(string $now, array $days, array $times, string $expected): void
    {
        $generator = $this->createFrequencyGenerator($now);

        $result = $generator->nextInEveryMonth($days, $this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $result = $generator->nextInEveryMonth($days, $this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryMonth($days, $this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);

        $result = $generator->nextInEveryMonth($days, $this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);
    }

    /**
     * @return array<array{string, array<int|string>, string[], string}>
     */
    public function getTestNextInEveryMonthProvider(): array
    {
        return [
            ['2020-10-01 15:00:00', [], [], '2020-11-01 00:00:00'],
            ['2020-10-15 15:00:00', [], [], '2020-11-01 00:00:00'],
            ['2020-10-01 15:00:00', [2, 6], [], '2020-10-02 00:00:00'],
            ['2020-10-10 15:00:00', [15, 20], [], '2020-10-15 00:00:00'],
            ['2020-10-10 15:00:00', [5, 10, 15], ['22:10:20'], '2020-10-10 22:10:20'],
            ['2020-10-10 15:00:00', [5, 10, 15], ['13:10:20', '12:10:20', '14:10:20'], '2020-10-15 12:10:20'],

            // Last day of month
            ['2020-10-15 15:00:00', [31], [], '2020-10-31 00:00:00'],
            ['2020-09-15 15:00:00', [31], [], '2020-09-30 00:00:00'],
            ['2020-02-15 15:00:00', [31], [], '2020-02-29 00:00:00'],
            ['2021-02-15 15:00:00', [31], [], '2021-02-28 00:00:00'],

            // Next year
            ['2020-12-29 15:00:00', [], [], '2021-01-01 00:00:00'],

            ['2020-10-10 15:00:00', ['15', '20'], [], '2020-10-15 00:00:00'],
        ];
    }

    public function testNextInEveryMonthWithBadDay(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad day 32');

        $generator->nextInEveryMonth([32], []);
    }

    public function testNextInEveryMonthWithBadDayNotNumber(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad day fake');

        $generator->nextInEveryMonth(['fake'], []);
    }

    public function testNextInEveryMonthWithBadTime(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Times must be DateTimeInterface objects');

        $generator->nextInEveryMonth([], ['fake']); // @phpstan-ignore-line
    }

    public function testNextInEveryQuartEmpty(): void
    {
        $generator = $this->createFrequencyGenerator('2020-01-15 15:00:00');

        $result = $generator->nextInEveryQuart();
        $this->checkResultDate('2020-04-01 00:00:00', \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryQuart();
        $this->checkResultDate('2020-04-01 00:00:00', \DateTimeImmutable::class, $result);
    }

    /**
     * @param array<int|string> $monthOffsets
     * @param array<int|string> $daysInMonth
     * @param string[]          $times
     *
     * @dataProvider getTestNextInEveryQuartProvider
     */
    public function testNextInEveryQuart(string $now, array $monthOffsets, array $daysInMonth, array $times, string $expected): void
    {
        $generator = $this->createFrequencyGenerator($now);

        $result = $generator->nextInEveryQuart($monthOffsets, $daysInMonth, $this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $result = $generator->nextInEveryQuart($monthOffsets, $daysInMonth, $this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryQuart($monthOffsets, $daysInMonth, $this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);

        $result = $generator->nextInEveryQuart($monthOffsets, $daysInMonth, $this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);
    }

    /**
     * @return array<array{string, array<int|string>, array<int|string>, string[], string}>
     */
    public function getTestNextInEveryQuartProvider(): array
    {
        return [
            ['2020-01-15 15:00:00', [], [], [], '2020-04-01 00:00:00'],
            ['2020-01-15 15:00:00', [3, 1], [], [], '2020-03-01 00:00:00'],
            ['2020-01-15 15:00:00', [2], [], [], '2020-02-01 00:00:00'],

            ['2020-01-15 15:00:00', [], [10, 20], [], '2020-01-20 00:00:00'],
            ['2020-01-15 15:00:00', [3, 1], [10, 15, 20], [], '2020-01-20 00:00:00'],
            ['2020-01-15 15:00:00', [2], [10, 15, 20], [], '2020-02-10 00:00:00'],

            ['2020-01-15 15:00:00', [3, 1], [10, 15, 20], ['13:10:20'], '2020-01-20 13:10:20'],
            ['2020-01-15 15:00:00', [3, 1], [10, 15, 20], ['22:10:20'], '2020-01-15 22:10:20'],

            // Last day of month
            ['2020-09-15 15:00:00', [2], [31], [], '2020-11-30 00:00:00'],
            ['2020-01-15 15:00:00', [2], [31], [], '2020-02-29 00:00:00'],
            ['2021-01-15 15:00:00', [2], [31], [], '2021-02-28 00:00:00'],

            // Next year
            ['2020-12-29 15:00:00', [2], [], [], '2021-02-01 00:00:00'],

            ['2020-01-15 15:00:00', ['3', '1'], ['10', '15', '20'], [], '2020-01-20 00:00:00'],
        ];
    }

    public function testNextInEveryQuartWithBadMonthOffset(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad month offset 4');

        $generator->nextInEveryQuart([4], []);
    }

    public function testNextInEveryQuartWithBadMonthOffsetNotNumber(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad month offset fake');

        $generator->nextInEveryQuart(['fake'], []);
    }

    public function testNextInEveryQuartWithBadDay(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad day 32');

        $generator->nextInEveryQuart([], [32]);
    }

    public function testNextInEveryQuartWithBadDayNotNumber(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad day fake');

        $generator->nextInEveryQuart([], ['fake']);
    }

    public function testNextInEveryQuartBadTime(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Times must be DateTimeInterface objects');

        $generator->nextInEveryQuart([], [], ['fake']); // @phpstan-ignore-line
    }

    public function testNextInEveryHalfYearEmpty(): void
    {
        $generator = $this->createFrequencyGenerator('2020-01-15 15:00:00');

        $result = $generator->nextInEveryHalfYear();
        $this->checkResultDate('2020-07-01 00:00:00', \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryHalfYear();
        $this->checkResultDate('2020-07-01 00:00:00', \DateTimeImmutable::class, $result);
    }

    /**
     * @param array<int|string> $monthOffsets
     * @param array<int|string> $daysInMonth
     * @param string[]          $times
     *
     * @dataProvider getTestNextInEveryHalfYearProvider
     */
    public function testNextInEveryHalfYear(string $now, array $monthOffsets, array $daysInMonth, array $times, string $expected): void
    {
        $generator = $this->createFrequencyGenerator($now);

        $result = $generator->nextInEveryHalfYear($monthOffsets, $daysInMonth, $this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $result = $generator->nextInEveryHalfYear($monthOffsets, $daysInMonth, $this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryHalfYear($monthOffsets, $daysInMonth, $this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);

        $result = $generator->nextInEveryHalfYear($monthOffsets, $daysInMonth, $this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);
    }

    /**
     * @return array<array{string, array<int|string>, array<int|string>, string[], string}>
     */
    public function getTestNextInEveryHalfYearProvider(): array
    {
        return [
            ['2020-01-15 15:00:00', [], [], [], '2020-07-01 00:00:00'],
            ['2020-01-15 15:00:00', [3, 1], [], [], '2020-03-01 00:00:00'],
            ['2020-01-15 15:00:00', [2], [], [], '2020-02-01 00:00:00'],

            ['2020-01-15 15:00:00', [], [10, 20], [], '2020-01-20 00:00:00'],
            ['2020-01-15 15:00:00', [3, 1], [10, 15, 20], [], '2020-01-20 00:00:00'],
            ['2020-01-15 15:00:00', [2], [10, 15, 20], [], '2020-02-10 00:00:00'],

            ['2020-01-15 15:00:00', [3, 1], [10, 15, 20], ['13:10:20'], '2020-01-20 13:10:20'],
            ['2020-01-15 15:00:00', [3, 1], [10, 15, 20], ['22:10:20'], '2020-01-15 22:10:20'],

            // Last day of month
            ['2020-11-15 15:00:00', [4], [31], [], '2021-04-30 00:00:00'],
            ['2020-01-15 15:00:00', [2], [31], [], '2020-02-29 00:00:00'],
            ['2021-01-15 15:00:00', [2], [31], [], '2021-02-28 00:00:00'],

            // Next year
            ['2020-12-29 15:00:00', [2], [], [], '2021-02-01 00:00:00'],

            ['2020-01-15 15:00:00', ['3', '1'], ['10', '15', '20'], [], '2020-01-20 00:00:00'],
        ];
    }

    public function testNextInEveryHalfYearWithBadMonthOffset(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad month offset 7');

        $generator->nextInEveryHalfYear([7], []);
    }

    public function testNextInEveryHalfYearWithBadMonthOffsetNotNumber(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad month offset fake');

        $generator->nextInEveryHalfYear(['fake'], []);
    }

    public function testNextInEveryHalfYearWithBadDay(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad day 32');

        $generator->nextInEveryHalfYear([], [32]);
    }

    public function testNextInEveryHalfYearWithBadDayNotNumber(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad day fake');

        $generator->nextInEveryHalfYear([], ['fake']);
    }

    public function testNextInEveryMonthHalfYearBadTime(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Times must be DateTimeInterface objects');

        $generator->nextInEveryHalfYear([], [], ['fake']); // @phpstan-ignore-line
    }

    public function testNextInEveryYearEmpty(): void
    {
        $generator = $this->createFrequencyGenerator('2020-01-15 15:00:00');

        $result = $generator->nextInEveryYear();
        $this->checkResultDate('2021-01-01 00:00:00', \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryYear();
        $this->checkResultDate('2021-01-01 00:00:00', \DateTimeImmutable::class, $result);
    }

    /**
     * @param array<int|string> $monthOffsets
     * @param array<int|string> $daysInMonth
     * @param string[]          $times
     *
     * @dataProvider getTestNextInEveryYearProvider
     */
    public function testNextInEveryYear(string $now, array $monthOffsets, array $daysInMonth, array $times, string $expected): void
    {
        $generator = $this->createFrequencyGenerator($now);

        $result = $generator->nextInEveryYear($monthOffsets, $daysInMonth, $this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $result = $generator->nextInEveryYear($monthOffsets, $daysInMonth, $this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTime::class, $result);

        $generator->generateDateTimeImmutable(true);

        $result = $generator->nextInEveryYear($monthOffsets, $daysInMonth, $this->createDates($times, \DateTime::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);

        $result = $generator->nextInEveryYear($monthOffsets, $daysInMonth, $this->createDates($times, \DateTimeImmutable::class));
        $this->checkResultDate($expected, \DateTimeImmutable::class, $result);
    }

    /**
     * @return array<array{string, array<int|string>, array<int|string>, string[], string}>
     */
    public function getTestNextInEveryYearProvider(): array
    {
        return [
            ['2020-01-15 15:00:00', [], [], [], '2021-01-01 00:00:00'],
            ['2020-01-15 15:00:00', [3, 1], [], [], '2020-03-01 00:00:00'],
            ['2020-01-15 15:00:00', [2], [], [], '2020-02-01 00:00:00'],

            ['2020-01-15 15:00:00', [], [10, 20], [], '2020-01-20 00:00:00'],
            ['2020-01-15 15:00:00', [3, 1], [10, 15, 20], [], '2020-01-20 00:00:00'],
            ['2020-01-15 15:00:00', [2], [10, 15, 20], [], '2020-02-10 00:00:00'],

            ['2020-01-15 15:00:00', [3, 1], [10, 15, 20], ['13:10:20'], '2020-01-20 13:10:20'],
            ['2020-01-15 15:00:00', [3, 1], [10, 15, 20], ['22:10:20'], '2020-01-15 22:10:20'],

            // Last day of month
            ['2020-11-15 15:00:00', [4], [31], [], '2021-04-30 00:00:00'],
            ['2020-01-15 15:00:00', [2], [31], [], '2020-02-29 00:00:00'],
            ['2020-06-18 15:00:00', [2], [31], [], '2021-02-28 00:00:00'],

            // Next year
            ['2020-12-29 15:00:00', [2], [], [], '2021-02-01 00:00:00'],

            ['2020-01-15 15:00:00', ['3', '1'], ['10', '15', '20'], [], '2020-01-20 00:00:00'],
        ];
    }

    public function testNextInEveryYearWithBadMonthOffset(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad month offset 13');

        $generator->nextInEveryYear([13], []);
    }

    public function testNextInEveryYearWithBadMonthOffsetNotNumber(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad month offset fake');

        $generator->nextInEveryYear(['fake'], []);
    }

    public function testNextInEveryearWithBadDay(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad day 32');

        $generator->nextInEveryYear([], [32]);
    }

    public function testNextInEveryYearWithBadDayNotNumber(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Bad day fake');

        $generator->nextInEveryYear([], ['fake']);
    }

    public function testNextInEveryYearBadTime(): void
    {
        $generator = $this->createFrequencyGenerator('now');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Times must be DateTimeInterface objects');

        $generator->nextInEveryYear([], [], ['fake']); // @phpstan-ignore-line
    }

    /**
     * @param string[] $frequencies
     *
     * @dataProvider getTestGetNextFrequencyProvider
     */
    public function testGetNextFrequency(array $frequencies, string $expected): void
    {
        $generator = $this->createFrequencyGenerator('2020-10-01 15:00:00');

        $reflection = new \ReflectionClass(FrequencyGenerator::class);
        $getNextFrequencyMethod = $reflection->getMethod('getNextFrequency');
        $getNextFrequencyMethod->setAccessible(true);

        /** @var \DateTime $result */
        $result = $getNextFrequencyMethod->invoke($generator, $this->createDates($frequencies, \DateTime::class));
        $this->checkResultDate($expected, \DateTime::class, $result);
    }

    /**
     * @return array<array{string[], string}>
     */
    public function getTestGetNextFrequencyProvider(): array
    {
        return [
            [['2020-10-01 16:00:00', '2020-10-01 17:00:00', '2020-10-02 15:00:00'], '2020-10-01 16:00:00'],
            [['2020-10-02 15:00:00', '2020-10-01 17:00:00', '2020-10-01 16:00:00'], '2020-10-01 16:00:00'],
            [['2020-09-01 16:00:00', '2020-09-01 17:00:00', '2020-10-02 15:00:00'], '2020-10-02 15:00:00'],
            [['2020-09-01 16:00:00', '2020-09-01 17:00:00', '2020-09-02 15:00:00'], '2020-10-01 15:00:00'],
        ];
    }

    /**
     * @param class-string $expectedClass
     */
    protected function checkResultDate(string $expectedDate, string $expectedClass, \DateTimeInterface $result): void
    {
        $this->assertInstanceOf($expectedClass, $result);
        $this->assertEquals(new $expectedClass($expectedDate), $result);
    }

    /**
     * @template T of \DateTimeInterface
     *
     * @param string[]        $dates
     * @param class-string<T> $class
     *
     * @return T[]
     */
    protected function createDates(array $dates, string $class): array
    {
        $dateObjects = [];
        foreach ($dates as $date) {
            $dateObjects[] = new $class($date);
        }

        return $dateObjects;
    }

    protected function createFrequencyGenerator(string $date): FrequencyGenerator
    {
        $generator = $this->getMockBuilder(FrequencyGenerator::class)
            ->onlyMethods(['getNow'])
            ->getMock();
        $generator->method('getNow')->willReturnCallback(static fn () => new \DateTime($date));

        return $generator;
    }
}
