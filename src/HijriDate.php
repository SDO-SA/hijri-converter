<?php

declare(strict_types=1);

namespace SDOSA;

use SDOSA\Data\UmmAlQura;
use SDOSA\Exceptions\DateOutOfRange;
use SDOSA\Exceptions\InvalidHijriDate;
use SDOSA\Locales\LocaleRegistry;
use SDOSA\Support\Julian;

/**
 * An immutable Umm al-Qura Hijri date.
 *
 * Usually obtained via {@see Hijri::toHijri()}; can also be constructed directly.
 */
final class HijriDate implements \Stringable
{
    public function __construct(
        private readonly int $year,
        private readonly int $month,
        private readonly int $day,
    ) {
        $this->validate();
    }

    /** Parse a "YYYY-MM-DD" string. */
    public static function fromString(string $date): self
    {
        [$y, $m, $d] = self::parseParts($date);

        return new self($y, $m, $d);
    }

    /** Today's Hijri date (system timezone). */
    public static function today(): self
    {
        return GregorianDate::today()->toHijri();
    }

    public function year(): int
    {
        return $this->year;
    }

    public function month(): int
    {
        return $this->month;
    }

    public function day(): int
    {
        return $this->day;
    }

    /** Number of days in this date's month (29 or 30). */
    public function monthLength(): int
    {
        $i = $this->monthIndex();

        return UmmAlQura::MONTH_STARTS[$i + 1] - UmmAlQura::MONTH_STARTS[$i];
    }

    /** Number of days in this date's year (354 or 355). */
    public function yearLength(): int
    {
        $first = self::monthIndexOf($this->year, 1);
        $next = self::monthIndexOf($this->year + 1, 1);

        return UmmAlQura::MONTH_STARTS[$next] - UmmAlQura::MONTH_STARTS[$first];
    }

    public function toJulian(): int
    {
        $rjd = UmmAlQura::MONTH_STARTS[$this->monthIndex()] + $this->day - 1;

        return Julian::rjdToJdn($rjd);
    }

    public function toGregorian(): GregorianDate
    {
        return GregorianDate::fromOrdinal(Julian::jdnToOrdinal($this->toJulian()));
    }

    /** ISO weekday: 1 (Monday) .. 7 (Sunday). */
    public function isoWeekday(): int
    {
        $ordinal = Julian::jdnToOrdinal($this->toJulian());

        return (($ordinal - 1) % 7) + 1;
    }

    /** Weekday: 0 (Monday) .. 6 (Sunday). */
    public function weekday(): int
    {
        return $this->isoWeekday() - 1;
    }

    public function monthName(?string $locale = null): string
    {
        return LocaleRegistry::get($locale)->hijriMonthName($this->month);
    }

    public function dayName(?string $locale = null): string
    {
        return LocaleRegistry::get($locale)->dayName($this->isoWeekday());
    }

    public function notation(?string $locale = null): string
    {
        return LocaleRegistry::get($locale)->hijriNotation();
    }

    /** "DD<sep>MM<sep>YYYY" (zero-padded unless $padding is false). */
    public function format(string $separator = '/', bool $padding = true): string
    {
        $parts = $padding
            ? [sprintf('%02d', $this->day), sprintf('%02d', $this->month), sprintf('%04d', $this->year)]
            : [$this->day, $this->month, $this->year];

        return implode($separator, $parts);
    }

    /** "YYYY-MM-DD". */
    public function toIso(): string
    {
        return sprintf('%04d-%02d-%02d', $this->year, $this->month, $this->day);
    }

    public function equals(self $other): bool
    {
        return $this->year === $other->year
            && $this->month === $other->month
            && $this->day === $other->day;
    }

    /** -1, 0, or 1. */
    public function compareTo(self $other): int
    {
        return $this->toJulian() <=> $other->toJulian();
    }

    public function __toString(): string
    {
        return $this->toIso();
    }

    private function monthIndex(): int
    {
        return self::monthIndexOf($this->year, $this->month);
    }

    private static function monthIndexOf(int $year, int $month): int
    {
        return ($year - 1) * 12 + ($month - 1) - UmmAlQura::HIJRI_OFFSET;
    }

    private function validate(): void
    {
        if ($this->month < 1 || $this->month > 12) {
            throw new InvalidHijriDate("Hijri month must be in 1..12, got {$this->month}.");
        }

        [$min, $max] = UmmAlQura::HIJRI_RANGE;
        if ($this->year < $min[0] || $this->year > $max[0]) {
            throw DateOutOfRange::hijri($this->year, [$min[0], $max[0]]);
        }

        $length = $this->monthLength();
        if ($this->day < 1 || $this->day > $length) {
            throw new InvalidHijriDate(
                "Hijri day must be in 1..{$length} for {$this->year}-{$this->month}, got {$this->day}."
            );
        }
    }

    /** @return array{0:int,1:int,2:int} */
    private static function parseParts(string $date): array
    {
        if (!preg_match('/^(\d{1,4})-(\d{1,2})-(\d{1,2})$/', trim($date), $m)) {
            throw new InvalidHijriDate("Cannot parse Hijri date '{$date}'; expected YYYY-MM-DD.");
        }

        return [(int) $m[1], (int) $m[2], (int) $m[3]];
    }
}
