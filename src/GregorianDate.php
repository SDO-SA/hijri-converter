<?php

declare(strict_types=1);

namespace SDOSA;

use SDOSA\Data\UmmAlQura;
use SDOSA\Exceptions\DateOutOfRange;
use SDOSA\Exceptions\InvalidGregorianDate;
use SDOSA\Locales\LocaleRegistry;
use SDOSA\Support\FormatsDate;
use SDOSA\Support\GregorianMath;
use SDOSA\Support\Julian;
use SDOSA\Support\Math;

/**
 * An immutable Gregorian date within the supported Umm al-Qura span.
 *
 * Build one with a named constructor — never `new`:
 *
 *     GregorianDate::make(1982, 12, 2);      // from parts
 *     GregorianDate::fromString('1982-12-02');
 *     GregorianDate::fromDateTime($carbon);  // any DateTimeInterface
 *     GregorianDate::today();
 *
 * Most callers get one back from {@see Hijri::toGregorian()} instead.
 */
final class GregorianDate implements \Stringable
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
            ?? throw new InvalidGregorianDate("Cannot parse Gregorian date '{$date}'; expected YYYY-MM-DD.");

        return self::make(...$parts);
    }

    /** Build from any DateTime / Carbon instance (date part only). */
    public static function fromDateTime(\DateTimeInterface $date): self
    {
        return self::make((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'));
    }

    /** Today's date (system timezone). */
    public static function today(): self
    {
        return self::fromDateTime(new \DateTimeImmutable('today'));
    }

    /** @internal Build from a proleptic Gregorian ordinal (used by Hijri conversion). */
    public static function fromOrdinal(int $ordinal): self
    {
        [$y, $m, $d] = GregorianMath::ordinalToYmd($ordinal);

        return self::make($y, $m, $d);
    }

    public function toJulian(): int
    {
        return Julian::ordinalToJdn(GregorianMath::ymdToOrdinal($this->year, $this->month, $this->day));
    }

    public function toHijri(): HijriDate
    {
        $rjd = Julian::jdnToRjd($this->toJulian());
        $index = Math::bisectRight(UmmAlQura::MONTH_STARTS, $rjd) - 1;

        $months = $index + UmmAlQura::HIJRI_OFFSET;
        $years = intdiv($months, 12);
        $year = $years + 1;
        $month = $months - $years * 12 + 1;
        $day = $rjd - UmmAlQura::MONTH_STARTS[$index] + 1;

        return HijriDate::make($year, $month, $day);
    }

    public function toDateTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->toIso());
    }

    /**
     * Convert to a Carbon instance. Requires nesbot/carbon to be installed.
     *
     * @return \Carbon\CarbonImmutable
     */
    public function toCarbon(): object
    {
        if (!class_exists(\Carbon\CarbonImmutable::class)) {
            throw new \RuntimeException('nesbot/carbon is required for toCarbon(); run "composer require nesbot/carbon".');
        }

        return \Carbon\CarbonImmutable::parse($this->toIso());
    }

    public function monthName(?string $locale = null): string
    {
        return LocaleRegistry::get($locale)->gregorianMonthName($this->month);
    }

    public function dayName(?string $locale = null): string
    {
        return LocaleRegistry::get($locale)->dayName($this->isoWeekday());
    }

    public function notation(?string $locale = null): string
    {
        return LocaleRegistry::get($locale)->gregorianNotation();
    }

    private function validate(): void
    {
        if ($this->month < 1 || $this->month > 12) {
            throw new InvalidGregorianDate("Gregorian month must be in 1..12, got {$this->month}.");
        }

        $length = GregorianMath::daysInMonth($this->year, $this->month);
        if ($this->day < 1 || $this->day > $length) {
            throw new InvalidGregorianDate(
                "Gregorian day must be in 1..{$length} for {$this->year}-{$this->month}, got {$this->day}."
            );
        }

        [$min, $max] = UmmAlQura::GREGORIAN_RANGE;
        $ordinal = GregorianMath::ymdToOrdinal($this->year, $this->month, $this->day);
        $minOrd = GregorianMath::ymdToOrdinal(...$min);
        $maxOrd = GregorianMath::ymdToOrdinal(...$max);
        if ($ordinal < $minOrd || $ordinal > $maxOrd) {
            throw DateOutOfRange::gregorian($this->toIso(), [
                sprintf('%04d-%02d-%02d', ...$min),
                sprintf('%04d-%02d-%02d', ...$max),
            ]);
        }
    }
}
