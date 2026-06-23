<?php

declare(strict_types=1);

namespace SDOSA\Laravel;

use SDOSA\GregorianDate;

/**
 * Registers Carbon macros so a Carbon instance can convert itself to Hijri.
 *
 * Macros are added to the concrete Carbon classes (which carry the Macroable
 * trait); the interface itself has no macro() method.
 */
final class CarbonMacros
{
    public static function register(): void
    {
        $toHijri = function (): \SDOSA\HijriDate {
            /** @var \Carbon\CarbonInterface $this */
            return GregorianDate::fromDateTime($this)->toHijri();
        };

        $hijriFormat = function (string $separator = '/', bool $padding = true): string {
            /** @var \Carbon\CarbonInterface $this */
            return GregorianDate::fromDateTime($this)->toHijri()->format($separator, $padding);
        };

        foreach ([\Carbon\Carbon::class, \Carbon\CarbonImmutable::class] as $class) {
            if (!class_exists($class) || !method_exists($class, 'macro')) {
                continue;
            }
            if (!$class::hasMacro('toHijri')) {
                $class::macro('toHijri', $toHijri);
            }
            if (!$class::hasMacro('hijriFormat')) {
                $class::macro('hijriFormat', $hijriFormat);
            }
        }
    }
}
