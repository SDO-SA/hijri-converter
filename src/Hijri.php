<?php

declare(strict_types=1);

namespace SDOSA;

/**
 * The package's single entry point.
 *
 * One class, two conversions — works the same in plain PHP and Laravel:
 *
 *     use SDOSA\Hijri;
 *
 *     Hijri::toHijri('1982-12-02');          // HijriDate  (1403-02-17)
 *     Hijri::toGregorian('1403-02-17');      // GregorianDate (1982-12-02)
 *     Hijri::toHijri(new DateTimeImmutable); // accepts DateTime / Carbon
 *     Hijri::today();                        // HijriDate
 *
 * The returned value objects carry the rich API (format, monthName, dayName,
 * notation, weekday, toCarbon, ...). Construct them directly if you prefer.
 */
final class Hijri
{
    /** Convert a Gregorian date (string, DateTime, or GregorianDate) to Hijri. */
    public static function toHijri(GregorianDate|\DateTimeInterface|string $date): HijriDate
    {
        return self::asGregorian($date)->toHijri();
    }

    /** Convert a Hijri date ("YYYY-MM-DD" string or HijriDate) to Gregorian. */
    public static function toGregorian(HijriDate|string $date): GregorianDate
    {
        $hijri = $date instanceof HijriDate ? $date : HijriDate::fromString($date);

        return $hijri->toGregorian();
    }

    /** Today's date as Hijri. */
    public static function today(): HijriDate
    {
        return HijriDate::today();
    }

    private static function asGregorian(GregorianDate|\DateTimeInterface|string $date): GregorianDate
    {
        return match (true) {
            $date instanceof GregorianDate => $date,
            $date instanceof \DateTimeInterface => GregorianDate::fromDateTime($date),
            default => GregorianDate::fromString($date),
        };
    }
}
