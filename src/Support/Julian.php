<?php

declare(strict_types=1);

namespace SDOSA\Support;

/**
 * Conversions between Julian Day Number (JDN), Gregorian proleptic ordinal,
 * and Reduced Julian Day (RJD) number.
 *
 * @internal
 */
final class Julian
{
    /** JDN of Gregorian ordinal day 1 (0001-01-01), minus one. */
    private const ORDINAL_EPOCH = 1721425;

    /** Offset between JDN and RJD. */
    private const RJD_EPOCH = 2400000;

    public static function jdnToOrdinal(int $jdn): int
    {
        return $jdn - self::ORDINAL_EPOCH;
    }

    public static function ordinalToJdn(int $ordinal): int
    {
        return $ordinal + self::ORDINAL_EPOCH;
    }

    public static function jdnToRjd(int $jdn): int
    {
        return $jdn - self::RJD_EPOCH;
    }

    public static function rjdToJdn(int $rjd): int
    {
        return $rjd + self::RJD_EPOCH;
    }
}
