<?php

declare(strict_types=1);

namespace SDOSA;

use SDOSA\Data\UmmAlQura;
use SDOSA\Exceptions\DateOutOfRange;
use SDOSA\Exceptions\InvalidHijriDate;
use SDOSA\Locales\LocaleRegistry;
use SDOSA\Support\FormatsDate;
use SDOSA\Support\Julian;

/**
 * An immutable Umm al-Qura Hijri date.
 *
 * Build one with a named constructor — never `new`:
 *
 *     HijriDate::make(1403, 2, 17);      // from parts
 *     HijriDate::fromString('1403-02-17');
 *     HijriDate::today();
 *
 * Most callers get one back from {@see Hijri::toHijri()} instead.
 */
final class HijriDate implements \Stringable
{
    use FormatsDate;

    private function __construct(
        private readonly int $year,
        private readonly int $month,
        private readonly int $day,
    ) {
        $this->validate();
    }

    /** Build from year, month, and day. */
    public static function make(int $year, int $month, int $day): self
    {
        return new self($year, $month, $day);
    }

    /** Parse a "YYYY-MM-DD" string. */
    public static function fromString(string $date): self
    {
        $parts = self::splitYmd($date)
            ?? throw new InvalidHijriDate("Cannot parse Hijri date '{$date}'; expected YYYY-MM-DD.");

        return self::make(...$parts);
    }

    /** Today's Hijri date (system timezone). */
    public static function today(): self
    {
        return GregorianDate::today()->toHijri();
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
}
