<?php

declare(strict_types=1);

namespace SDOSA\Exceptions;

/** Thrown when a date falls outside the supported Umm al-Qura range. */
final class DateOutOfRange extends HijriException
{
    /** @param array{0:string,1:string} $range The inclusive [min, max] bound that was violated. */
    public static function gregorian(string $iso, array $range): self
    {
        return new self("Gregorian date {$iso} is out of range {$range[0]}..{$range[1]}.");
    }

    /** @param array{0:int,1:int} $range The inclusive [min, max] year bound that was violated. */
    public static function hijri(int $year, array $range): self
    {
        return new self("Hijri year {$year} is out of range {$range[0]}..{$range[1]}.");
    }
}
