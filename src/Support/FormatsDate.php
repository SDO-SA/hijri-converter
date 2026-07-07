<?php

declare(strict_types=1);

namespace SDOSA\Support;

/**
 * Shared behaviour for the immutable date value objects (HijriDate,
 * GregorianDate): accessors, weekday math, formatting, and comparison.
 *
 * A using class must declare `private readonly int $year/$month/$day`, a
 * `toJulian(): int` method, and `monthName()` / `notation()`; everything else
 * is provided here so the two calendars stay identical where they should be.
 *
 * @internal
 */
trait FormatsDate
{
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

    /** ISO weekday: 1 (Monday) .. 7 (Sunday). */
    public function isoWeekday(): int
    {
        $ordinal = Julian::jdnToOrdinal($this->toJulian());

        return (($ordinal - 1) % 7) + 1;
    }

    /**
     * Day of the week on the Saudi/Islamic week, which starts on Sunday:
     * 1 (Sunday) .. 7 (Saturday). Use {@see isoWeekday()} for the ISO
     * (Monday-first) numbering.
     */
    public function weekday(): int
    {
        return ($this->isoWeekday() % 7) + 1;
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

    /**
     * Full localized date including the calendar notation, e.g.
     * "17 Safar 1403 AH" or "17 صفر 1403 هـ".
     */
    public function longFormat(?string $locale = null): string
    {
        return sprintf('%d %s %d %s', $this->day, $this->monthName($locale), $this->year, $this->notation($locale));
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

    /**
     * Split a "YYYY-MM-DD" string into integer parts, or null if malformed.
     * Callers turn null into their own calendar-specific exception.
     *
     * @return array{0:int,1:int,2:int}|null
     */
    private static function splitYmd(string $date): ?array
    {
        if (!preg_match('/^(\d{1,4})-(\d{1,2})-(\d{1,2})$/', trim($date), $m)) {
            return null;
        }

        return [(int) $m[1], (int) $m[2], (int) $m[3]];
    }
}
