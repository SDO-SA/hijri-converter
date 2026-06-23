<?php

declare(strict_types=1);

namespace SDOSA\Locales;

/**
 * Translations for month names, weekday names, and calendar notations.
 *
 * Concrete locales provide the data arrays; this base provides the lookups.
 */
abstract class Locale
{
    /** 2-char language tag, e.g. "en". */
    abstract public function tag(): string;

    /** @return string[] 12 Hijri month names, indexed 0..11. */
    abstract protected function hijriMonths(): array;

    /** @return string[] 12 Gregorian month names, indexed 0..11. */
    abstract protected function gregorianMonths(): array;

    /** @return string[] 7 weekday names, Monday..Sunday, indexed 0..6. */
    abstract protected function weekdays(): array;

    /** Notation suffix for Hijri dates, e.g. "AH". */
    abstract public function hijriNotation(): string;

    /** Notation suffix for Gregorian dates, e.g. "CE". */
    abstract public function gregorianNotation(): string;

    public function hijriMonthName(int $month): string
    {
        return $this->hijriMonths()[$month - 1];
    }

    public function gregorianMonthName(int $month): string
    {
        return $this->gregorianMonths()[$month - 1];
    }

    /** @param int $isoWeekday 1 (Monday) .. 7 (Sunday). */
    public function dayName(int $isoWeekday): string
    {
        return $this->weekdays()[$isoWeekday - 1];
    }
}
