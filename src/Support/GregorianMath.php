<?php

declare(strict_types=1);

namespace SDOSA\Support;

/**
 * Proleptic Gregorian calendar arithmetic, ported from the reference algorithm
 * (CPython's datetime). Days are counted as ordinals where 0001-01-01 is day 1.
 *
 * @internal
 */
final class GregorianMath
{
    /** Days in each month of a non-leap year, indexed 1..12. */
    private const DAYS_IN_MONTH = [1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    /** Cumulative days before the first of each month in a non-leap year, indexed 1..12. */
    private const DAYS_BEFORE_MONTH = [1 => 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

    /** Number of days in 400 / 100 / 4 year cycles (= daysBeforeYear(401|101|5)). */
    private const DI400Y = 146097;
    private const DI100Y = 36524;
    private const DI4Y = 1461;

    public static function isLeap(int $year): bool
    {
        return $year % 4 === 0 && ($year % 100 !== 0 || $year % 400 === 0);
    }

    public static function daysInMonth(int $year, int $month): int
    {
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException("Month must be in 1..12, got {$month}.");
        }
        if ($month === 2 && self::isLeap($year)) {
            return 29;
        }

        return self::DAYS_IN_MONTH[$month];
    }

    /** Number of days before January 1st of the given year. */
    private static function daysBeforeYear(int $year): int
    {
        $y = $year - 1;

        return $y * 365 + intdiv($y, 4) - intdiv($y, 100) + intdiv($y, 400);
    }

    /** Convert a Gregorian (year, month, day) to a proleptic ordinal day number. */
    public static function ymdToOrdinal(int $year, int $month, int $day): int
    {
        $daysBeforeMonth = self::DAYS_BEFORE_MONTH[$month]
            + ($month > 2 && self::isLeap($year) ? 1 : 0);

        return self::daysBeforeYear($year) + $daysBeforeMonth + $day;
    }

    /**
     * Convert a proleptic ordinal day number to a Gregorian (year, month, day).
     *
     * @return array{0:int,1:int,2:int}
     */
    public static function ordinalToYmd(int $n): array
    {
        $n -= 1;

        [$n400, $n] = Math::divmod($n, self::DI400Y);
        $year = $n400 * 400 + 1;

        [$n100, $n] = Math::divmod($n, self::DI100Y);
        [$n4, $n] = Math::divmod($n, self::DI4Y);
        [$n1, $n] = Math::divmod($n, 365);

        $year += $n100 * 100 + $n4 * 4 + $n1;

        if ($n1 === 4 || $n100 === 4) {
            // Last day of a leap-cycle boundary year.
            return [$year - 1, 12, 31];
        }

        $leapYear = $n1 === 3 && ($n4 !== 24 || $n100 === 3);
        $month = ($n + 50) >> 5;

        $preceding = self::DAYS_BEFORE_MONTH[$month] + ($month > 2 && $leapYear ? 1 : 0);
        if ($preceding > $n) {
            $month -= 1;
            $preceding -= self::DAYS_IN_MONTH[$month] + ($month === 2 && $leapYear ? 1 : 0);
        }
        $n -= $preceding;

        return [$year, $month, $n + 1];
    }
}
