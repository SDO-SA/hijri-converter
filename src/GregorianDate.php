<?php

declare(strict_types=1);

namespace SDOSA;

use SDOSA\Data\UmmAlQura;
use SDOSA\Exceptions\DateOutOfRange;
use SDOSA\Exceptions\InvalidGregorianDate;
use SDOSA\Locales\LocaleRegistry;
use SDOSA\Support\GregorianMath;
use SDOSA\Support\Julian;
use SDOSA\Support\Math;

/**
 * An immutable Gregorian date within the supported Umm al-Qura span.
 *
 * Usually obtained via {@see Hijri::toGregorian()}; can also be constructed directly.
 */
final class GregorianDate implements \Stringable
{
    public function __construct(
        private readonly int $year,
        private readonly int $month,
        private readonly int $day,
    ) {
        $this->validate();
    }

    public static function fromString(string $date): self
    {
        [$y, $m, $d] = self::parseParts($date);

        return new self($y, $m, $d);
    }

    public static function fromDateTime(\DateTimeInterface $date): self
    {
        return new self((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'));
    }

    public static function today(): self
    {
        return self::fromDateTime(new \DateTimeImmutable('today'));
    }

    /** @internal Build from a proleptic Gregorian ordinal (used by Hijri conversion). */
    public static function fromOrdinal(int $ordinal): self
    {
        [$y, $m, $d] = GregorianMath::ordinalToYmd($ordinal);

        return new self($y, $m, $d);
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

        return new HijriDate($year, $month, $day);
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

    public function isoWeekday(): int
    {
        $ordinal = GregorianMath::ymdToOrdinal($this->year, $this->month, $this->day);

        return (($ordinal - 1) % 7) + 1;
    }

    public function weekday(): int
    {
        return $this->isoWeekday() - 1;
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

    public function format(string $separator = '/', bool $padding = true): string
    {
        $parts = $padding
            ? [sprintf('%02d', $this->day), sprintf('%02d', $this->month), sprintf('%04d', $this->year)]
            : [$this->day, $this->month, $this->year];

        return implode($separator, $parts);
    }

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

    public function compareTo(self $other): int
    {
        return $this->toJulian() <=> $other->toJulian();
    }

    public function __toString(): string
    {
        return $this->toIso();
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

    /** @return array{0:int,1:int,2:int} */
    private static function parseParts(string $date): array
    {
        if (!preg_match('/^(\d{1,4})-(\d{1,2})-(\d{1,2})$/', trim($date), $m)) {
            throw new InvalidGregorianDate("Cannot parse Gregorian date '{$date}'; expected YYYY-MM-DD.");
        }

        return [(int) $m[1], (int) $m[2], (int) $m[3]];
    }
}
